<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Query;

use Vortos\Domain\Query\QueryInterface;

/**
 * Contract for the query bus.
 *
 * Routes a query to exactly one handler and returns its result.
 * Queries are always synchronous and always read-only.
 *
 * ## No transaction, no events, no outbox
 *
 * Query handlers must NOT modify state.
 * Query handlers must NOT dispatch events.
 * Query handlers do NOT run inside a UnitOfWork transaction.
 * They read from the read side (MongoDB) and return view models.
 *
 * ## Usage
 *
 *   $user = $queryBus->ask(new GetUserQuery(userId: $id));
 *
 * The return type is mixed — queries return whatever makes sense:
 *   - A single ViewModel array
 *   - A PageResult for paginated lists
 *   - A scalar (count, boolean)
 *   - Null if not found
 *
 * ## Separation from commands
 *
 * Commands change state, return nothing meaningful (void or aggregate).
 * Queries read state, return data, change nothing.
 * Never mix them — a method that both reads and writes is a command,
 * not a query. Return the ID from the command if you need it for a redirect,
 * then query for the full data separately.
 */
interface QueryBusInterface
{
    /**
     * Ask a question — dispatch a query to its registered handler.
     *
     * @param QueryInterface $query The query to dispatch
     * @return mixed                Whatever the query handler returns
     *
     * @throws QueryHandlerNotFoundException If no handler is registered for this query
     */
    public function ask(QueryInterface $query): mixed;
}
