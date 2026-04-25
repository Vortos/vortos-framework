<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Exception;

/**
 * Thrown when no handler is registered for a given command class.
 *
 * This is a programming error — every command must have exactly one handler.
 * If you see this in production, a handler registration is missing in services.php
 * or the #[AsCommandHandler] attribute is missing on the handler class.
 */
final class CommandHandlerNotFoundException extends \RuntimeException
{
    public function __construct(string $commandClass)
    {
        parent::__construct(sprintf(
            'No command handler registered for "%s". '
                . 'Add #[AsCommandHandler(handles: %s::class)] to your handler class '
                . 'and ensure it is registered as a service.',
            $commandClass,
            $commandClass,
        ));
    }
}
