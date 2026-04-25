<?php

declare(strict_types=1);

namespace Vortos\Messaging\Outbox\Exception;

use RuntimeException;

final class OutboxWriteException extends RuntimeException
{
    public static function forEvent(string $eventClass, string $transportName, \Throwable $previous): self
    {
        return new self(
            "Failed to write outbox entry for event '{$eventClass}' on transport '{$transportName}': " . $previous->getMessage(),
            0,
            $previous
        );
    }
}
