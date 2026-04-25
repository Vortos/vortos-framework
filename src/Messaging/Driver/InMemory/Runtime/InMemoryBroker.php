<?php

declare(strict_types=1);

namespace Vortos\Messaging\Driver\InMemory\Runtime;

use Vortos\Messaging\ValueObject\ReceivedMessage;

/**
 * Shared in-memory message store for the InMemory broker driver.
 * 
 * Holds all queued messages in memory keyed by transport name.
 * Registered as a singleton service — InMemoryProducer and InMemoryConsumer
 * both reference the same instance so produced messages are visible to consumers.
 * 
 * Always call reset() in test tearDown() to prevent message leakage between tests.
 */
final class InMemoryBroker
{
    /** @var array $queues<string, ReceivedMessage> */
    private array $queues = [];

    public function enqueue(string $transportName, ReceivedMessage $message):void
    {
        $this->queues[$transportName][] = $message;
    }

    public function dequeue(string $transportName): ?ReceivedMessage
    {
        if (empty($this->queues[$transportName])) {
            return null;
        }
        return array_shift($this->queues[$transportName]);
    }

    public function count(string $transportName): int
    {
        return count($this->queues[$transportName] ?? []);
    }

    public function all(string $transportName) : array
    {
        return $this->queues[$transportName] ?? [];
    }

    /**
     * Critical — call this in tearDown() in every test class. 
     * Without it, messages from one test leak into the next.
     */
    public function reset():void
    {
        $this->queues = [];
    }

    public function hasMessage(string $transportName) : bool
    {
        return !empty($this->queues[$transportName]);
    }
}