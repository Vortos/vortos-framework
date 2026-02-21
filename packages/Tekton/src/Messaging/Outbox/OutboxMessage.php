<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\Outbox;

use DateTimeImmutable;

/**
 * Represents a single outbox record fetched from the database.
 *
 * Produced by OutboxPoller::fetchPending() and consumed by OutboxRelayWorker.
 * Immutable — reflects the state of the database row at fetch time.
 * Use OutboxPoller::markPublished() or markFailed() to transition state.
 */
final readonly class OutboxMessage 
{
    public function __construct(
        public string $id,
        public string $transportName,
        public string $eventClass,
        public string $payload,
        public array $headers,

        /** One of: 'pending', 'published', 'failed' */
        public string $status, 
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $publishedAt,
        public int $attemptCount,
        public ?string $failureReason
    ){
    }

    /**
     * Construct from a raw DBAL database row.
     * Handles type coercion from database strings to PHP types.
     * Nullable columns (publishedAt, failureReason) default to null when absent.
     */
    public static function fromDatabaseRow(array $row):self
    {
        return new self(
            $row['id'],
            $row['transportName'],
            $row['eventClass'],
            $row['payload'],
            $row['headers'],
            $row['status'],
            new DateTimeImmutable($row['createdAt']),
            isset($row['publishedAt']) ? new DateTimeImmutable($row['publishedAt'])  :  null,
            $row['attemptCount'],
            $row['failureReason'] ?? null
        );
    }
}
