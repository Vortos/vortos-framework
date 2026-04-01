<?php

declare(strict_types=1);

namespace Vortos\Messaging\ValueObject;

use DateTimeImmutable;

/**
 * Represents a raw message received from a broker before deserialization.
 *
 * Broker-agnostic. Produced by broker-specific consumer implementations
 * and passed to the ConsumerRunner handler callable. Contains the raw payload,
 * headers, and broker-specific metadata needed for acknowledgement.
 * Immutable — never modified after construction.
 */
final readonly class ReceivedMessage
{
    public function __construct(
        public string $id, 
        public string $payload, 
        public array $headers, 
        public string $transportName, 
        public DateTimeImmutable $receivedAt, 
        public array $metadata = []
    ){
    }

    /**
     * Retrieve a value from broker-specific metadata by key.
     * For Kafka: valid keys are 'partition', 'offset', 'key', 'topic'.
     * Returns $default if the key is not present.
     */
    public function getMetadata(string $key, mixed $defaults = null):mixed
    {
        return $this->metadata[$key] ?? $defaults;
    }
}