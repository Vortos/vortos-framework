<?php

declare(strict_types=1);

namespace Vortos\Messaging\DeadLetter;

use DateTimeImmutable;

/**
 * Represents a message that has exhausted all retry attempts.
 *
 * Written to the failed_messages database table and optionally to a DLQ broker topic
 * by DeadLetterWriter. Used for operational visibility, alerting, and manual replay
 * via the vortos:dlq:replay CLI command.
 */
final readonly class DeadLetterMessage
{
    public function __construct(
        public string $id,
        public string $originalTransport,
        public string $eventClass,
        public string $payload,
        public array $headers,
        public string $failureReason,
        public string $exceptionClass,
        public int $attemptCount,
        public DateTimeImmutable $failedAt
    ) {}
}
