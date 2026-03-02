<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\Driver\InMemory\Runtime;

use DateTimeImmutable;
use Fortizan\Tekton\Messaging\Contract\DomainEventInterface;
use Fortizan\Tekton\Messaging\Contract\ProducerInterface;
use Fortizan\Tekton\Messaging\Serializer\SerializerLocator;
use Fortizan\Tekton\Messaging\ValueObject\ReceivedMessage;

/**
 * In-memory producer. Serializes events and enqueues them in InMemoryBroker.
 * 
 * Used in tests to simulate event production without a real broker.
 * Messages are immediately available for InMemoryConsumer to process.
 */
final class InMemoryProducer implements ProducerInterface
{
    public function __construct(
        private InMemoryBroker $broker,
        private SerializerLocator $serializerLocator
    ){
    }

    public function produce(string $transportName, DomainEventInterface $event, array $headers = []): void
    {
        $serializer = $this->serializerLocator->locate('json');
        $payload = $serializer->serialize($event);

        $eventClass = get_class($event);

        $finalHeaders = array_merge(
            ['event_class' => $eventClass],
            $headers
        );

        $receivedMessage = new ReceivedMessage(
            id: bin2hex(random_bytes(8)),
            payload: $payload,
            headers: $finalHeaders,
            transportName: $transportName,
            receivedAt: new DateTimeImmutable(),
            metadata: []
        );

        $this->broker->enqueue($transportName, $receivedMessage);
    }

    public function produceBatch(string $transportName, array $events, array $headers = []): void
    {
        foreach($events as $event){
            $this->produce($transportName, $event, $headers);
        }
    }
}