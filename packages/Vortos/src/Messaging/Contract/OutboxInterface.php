<?php

declare(strict_types=1);

namespace Vortos\Messaging\Contract;

use Vortos\Domain\Event\DomainEventInterface;

/**
 * Transactional outbox contract.
 *
 * Guarantees at-least-once delivery by writing events to a database table
 * within the same transaction as the domain change. The OutboxRelayWorker
 * then reads pending messages and produces them to the broker asynchronously.
 * Implementations must NOT open their own transaction — the caller owns it.
 */
interface OutboxInterface
{
    /**
     * Store an event in the outbox table within the caller's active transaction.
     * Never call this outside a transaction boundary.
     */ 
    public function store(DomainEventInterface $event, string $transportName, array $headers = []):void ;
}