<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\Driver\Kafka\Runtime;

use Fortizan\Tekton\Messaging\Contract\ConsumerInterface;
use Fortizan\Tekton\Messaging\Registry\ConsumerRegistry;
use Fortizan\Tekton\Messaging\ValueObject\ReceivedMessage;
use Psr\Log\LoggerInterface;
use RdKafka\KafkaConsumer as RdKafkaConsumer;

/**
 * Kafka implementation of ConsumerInterface using the RdKafka extension.
 * 
 * Runs a blocking poll loop with a 500ms timeout per iteration. The loop
 * exits cleanly when stop() is called (e.g. via SIGTERM signal handler).
 * 
 * Partition EOF and timeout errors are treated as normal conditions and
 * do not interrupt the loop. Only real errors are logged.
 * 
 * acknowledge() uses async commit for throughput. If you need guaranteed
 * offset commits before the process exits, call commitAsync() in your
 * shutdown handler.
 */
final class KafkaConsumer implements ConsumerInterface
{
    private RdKafkaConsumer $rdConsumer;
    private string $topic;
    private ConsumerRegistry $consumerRegistry;
    private LoggerInterface $logger;

    private bool $running = false;
    private bool $asyncCommit = true;

    public function __construct(RdKafkaConsumer $rdConsumer, string $topic, ConsumerRegistry $consumerRegistry, LoggerInterface $logger)
    {
        $this->rdConsumer = $rdConsumer;
        $this->topic = $topic;
        $this->consumerRegistry = $consumerRegistry;
        $this->logger = $logger;
    }
    
    public function consume(string $consumerName, callable $handler): void
    {
        $this->running = true;

        $this->asyncCommit = $this->consumerRegistry->get($consumerName)->toArray()['kafka']['asyncCommit'] ?? true;

        $this->rdConsumer->subscribe([$this->topic]);

        while ($this->running) {
            $rdMessage = $this->rdConsumer->consume(500);

            if ($rdMessage->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $handler(
                    KafkaMessage::fromRdKafkaMessage($rdMessage)
                        ->toReceivedMessage($consumerName)
                );
            } elseif (
                $rdMessage->err === RD_KAFKA_RESP_ERR__PARTITION_EOF ||
                $rdMessage->err === RD_KAFKA_RESP_ERR__TIMED_OUT
            ) {
                // Normal conditions — no messages available, continue polling
            } elseif ($rdMessage->err === RD_KAFKA_RESP_ERR__FATAL) {
                $this->logger->critical('Fatal Kafka error — consumer stopping', [
                    'error' => $rdMessage->errstr(),
                    'code'  => $rdMessage->err,
                ]);
                $this->stop();
            } else {
                $this->logger->error('Kafka consume error', [
                    'error' => $rdMessage->errstr(),
                    'code'  => $rdMessage->err,
                ]);
            }
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function acknowledge(ReceivedMessage $message): void
    {
        $this->commit();
    }

    public function reject(ReceivedMessage $message, bool $requeue = false): void
    {
        if($requeue){
            // Kafka requeue: offset not committed, message will be redelivered
        }else{
            $this->commit();
        }   
    }

    private function commit($message_or_offsets = null):void
    {
        if ($this->asyncCommit) {
            $this->rdConsumer->commitAsync($message_or_offsets);
        } else {
            $this->rdConsumer->commit($message_or_offsets);
        }
    }

    
}
