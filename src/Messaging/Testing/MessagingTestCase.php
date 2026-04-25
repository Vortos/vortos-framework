<?php

declare(strict_types=1);

namespace Vortos\Messaging\Testing;

use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryBroker;
use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryConsumer;
use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryProducer;
use Vortos\Messaging\Serializer\JsonSerializer;
use Vortos\Messaging\Serializer\SerializerLocator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Base test case for messaging integration tests.
 * 
 * Provides a pre-wired InMemoryBroker, InMemoryProducer, and InMemoryConsumer.
 * The broker is reset between every test via tearDown() — no message leakage.
 */
abstract class MessagingTestCase extends TestCase
{
    protected InMemoryBroker $broker;
    protected InMemoryProducer $producer;
    protected InMemoryConsumer $consumer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->broker = new InMemoryBroker();
        $this->producer = new InMemoryProducer($this->broker, $this->buildSerializerLocator());
        $this->consumer = new InMemoryConsumer($this->broker, new NullLogger());
    }

    protected function tearDown(): void
    {
        $this->broker->reset();
        parent::tearDown();
    }

    private function buildSerializerLocator(): SerializerLocator
    {
        return new SerializerLocator(
            [
                'json' => new JsonSerializer() 
            ]
        );
    }

    protected function assertEventPublished(string $transportName, string $eventClass, ?callable $matcher = null):void
    {
        $messages = $this->broker->all($transportName);

        foreach($messages as $message){
            if(($message->headers['event_class'] ?? null) === $eventClass){
                if($matcher === null || $matcher($message)){
                    $this->assertTrue(true);
                    return;
                }
            }
        }

        $this->fail(
            "Expected event '{$eventClass}' was not found on transport '{$transportName}'"
        );
    }

    protected function assertEventNotPublished(string $transportName, string $eventClass): void
    {
        $messages = $this->broker->all($transportName);

        foreach ($messages as $message) {
            if (($message->headers['event_class'] ?? null) === $eventClass) {
                $this->fail(
                    "Event '{$eventClass}' was found on transport '{$transportName}' but was not expected"
                );
            }
        }

        $this->assertTrue(true);
    }

    protected function consumeAll(string $consumerName, callable $handler): void
    {
        $this->consumer->consume($consumerName, $handler);
    }

    protected function publishedCount(string $transportName): int
    {
        return $this->broker->count($transportName);
    }
}