<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Query;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Vortos\Cqrs\Exception\QueryHandlerNotFoundException;
use Vortos\Domain\Query\QueryInterface;

/**
 * Default synchronous query bus implementation.
 *
 * Routes a query to its registered handler and returns the result.
 * No transaction. No event dispatch. No idempotency.
 * Query handlers are pure read operations.
 *
 * ## Handler discovery
 *
 * Handlers are discovered at compile time by QueryHandlerPass.
 * Stored in a ServiceLocator keyed by query class name.
 * The bus looks up the handler by the query's fully qualified class name.
 *
 * ## Caching (future)
 *
 * Query results can be cached by wrapping this bus with a caching decorator.
 * The decorator checks a cache store before calling ask() on the inner bus.
 * This is not implemented yet — add to backlog when needed.
 */
final class QueryBus implements QueryBusInterface
{
    public function __construct(private ServiceLocator $handlerLocator) {}

    /**
     * {@inheritdoc}
     */
    public function ask(QueryInterface $query): mixed
    {
        $queryClass = get_class($query);

        if (!$this->handlerLocator->has($queryClass)) {
            throw new QueryHandlerNotFoundException($queryClass);
        }

        $handler = $this->handlerLocator->get($queryClass);

        return $handler($query);
    }
}
