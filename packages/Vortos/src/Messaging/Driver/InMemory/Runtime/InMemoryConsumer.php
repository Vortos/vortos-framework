<?php

declare(strict_types=1);

namespace Vortos\Messaging\Driver\InMemory\Runtime;

use Vortos\Messaging\Contract\ConsumerInterface;
use Vortos\Messaging\ValueObject\ReceivedMessage;
use Psr\Log\LoggerInterface;

/**
 * In-memory consumer. Dequeues messages from InMemoryBroker and passes them to the handler.
 * 
 * Unlike KafkaConsumer, this does not run an infinite polling loop.
 * It processes all available messages and exits — making tests fast and deterministic.
 * Call stop() to exit early if needed.
 * 
 * acknowledge() is a no-op — messages are already consumed on dequeue().
 * reject() with requeue=true re-enqueues the message for reprocessing.
 */
final class InMemoryConsumer implements ConsumerInterface
{
    private bool $running = false;

    public function __construct(
        private InMemoryBroker $broker,
        private LoggerInterface $logger
    ) {}

    public function consume(string $consumerName, callable $handler): void
    {
        $this->running = true;

        while ($this->running) {
            $message = $this->broker->dequeue($consumerName);

            if($message === null){
                break;
            }

            $handler($message);
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function acknowledge(ReceivedMessage $message): void
    {
    }

    public function reject(ReceivedMessage $message, bool $requeue = false): void
    {
        if($requeue){
            $this->broker->enqueue($message->transportName, $message);
        }
    }
}
