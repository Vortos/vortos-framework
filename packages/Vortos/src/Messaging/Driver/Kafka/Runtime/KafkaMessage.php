<?php

declare(strict_types=1);

namespace Vortos\Messaging\Driver\Kafka\Runtime;

use DateTimeImmutable;
use Vortos\Messaging\ValueObject\ReceivedMessage;
use RdKafka\Message;

/**
 * Wraps a raw RdKafka Message into a framework value object.
 * Extracts all relevant Kafka metadata into typed properties.
 * Use toReceivedMessage() to convert to the broker-agnostic ReceivedMessage type
 * that the ConsumerRunner and ConsumerInterface work with.
 */
final readonly class KafkaMessage
{
    private function __construct(
        private string $payload,
        private string $topic,
        private int $partition,
        private int $offset,
        private array $headers,
        private int $timestamp,
        private string $key
    ) {}

    public static function fromRdKafkaMessage(Message $msg): self
    {
        return new self(
            $msg->payload ?? '',
            $msg->topic_name ?? '',
            $msg->partition,
            $msg->offset,
            $msg->headers ?? [],
            $msg->timestamp,
            $msg->key ?? ''
        );
    }

    public function toReceivedMessage(string $transportName): ReceivedMessage
    {
        return new ReceivedMessage(
            id: bin2hex(random_bytes(16)),
            payload: $this->payload,
            headers: $this->headers,
            transportName: $transportName,
            receivedAt: new DateTimeImmutable(),
            metadata: [
                'partition' => $this->partition,
                'offset' => $this->offset,
                'topic' => $this->topic,
                'key' => $this->key,
                'timestamp' => $this->timestamp
            ]   
        );
    }
}
