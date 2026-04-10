<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Exception;

/**
 * Thrown when a command is dispatched with an idempotency key
 * that was already processed and strict mode is enabled.
 *
 * In default (lenient) mode, duplicate commands are silently skipped.
 * In strict mode, this exception is thrown so the caller knows the
 * command was a duplicate. Enable strict mode in config/cqrs.php:
 *
 *   $config->commandBus()->strictIdempotency(true);
 */
final class DuplicateCommandException extends \RuntimeException
{
    public function __construct(string $idempotencyKey, string $commandClass)
    {
        parent::__construct(sprintf(
            'Command "%s" with idempotency key "%s" was already processed.',
            $commandClass,
            $idempotencyKey,
        ));
    }
}
