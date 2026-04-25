<?php

declare(strict_types=1);

namespace Vortos\Messaging\Driver\Kafka\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when producing a message to a Kafka topic fails.
 *
 * Contains the transport name and event class for debugging.
 * The original RdKafka exception is preserved as the previous exception
 * so the full error chain is available in logs and error tracking tools.
 */
final class ProducerException extends RuntimeException
{
    /**
     * Create an exception for a single event production failure.
     * Use this when a specific event class is known (single produce).
     */
    public static function forTransport(string $transportName, string $eventClass, Throwable $previous): self 
    {
        return new self(
            "Failed to produce event '{$eventClass}' to transport '{$transportName}': " . $previous->getMessage(),
            0,
            $previous
        );
    }

    /**
     * Create an exception for a batch flush failure.
     * Used when flush() fails after batch enqueue — no single event class is available.
     */
    public static function forBatchFlush(string $transportName, int $errorCode): self
    {
        return new self(
            "Failed to flush batch to transport '{$transportName}'. RdKafka error code: {$errorCode}",
            $errorCode
        );
    }
}