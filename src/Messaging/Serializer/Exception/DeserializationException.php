<?php

declare(strict_types=1);

namespace Vortos\Messaging\Serializer\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when a raw payload string cannot be deserialized into a domain event.
 * The original payload (truncated to 200 chars) and causing exception are preserved for debugging.
 */
final class DeserializationException extends RuntimeException 
{
    public static function forPayload(string $payload, string $eventClass, Throwable $previous):self
    {
        return new self(
            message: "Failed to deserialize payload into '{$eventClass}'. Payload: " . substr($payload, 0, 200),
            code:0,
            previous:$previous
        );
    }
}
