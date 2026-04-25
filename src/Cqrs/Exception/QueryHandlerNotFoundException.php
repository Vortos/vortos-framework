<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Exception;

/**
 * Thrown when no handler is registered for a given query class.
 *
 * This is a programming error — every query must have exactly one handler.
 */
final class QueryHandlerNotFoundException extends \RuntimeException
{
    public function __construct(string $queryClass)
    {
        parent::__construct(sprintf(
            'No query handler registered for "%s". '
                . 'Add #[AsQueryHandler(handles: %s::class)] to your handler class '
                . 'and ensure it is registered as a service.',
            $queryClass,
            $queryClass,
        ));
    }
}
