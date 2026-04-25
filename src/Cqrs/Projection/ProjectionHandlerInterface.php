<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Projection;

use Vortos\Domain\Event\DomainEventInterface;

/**
 * Contract for projection handlers.
 *
 * Projection handlers consume domain events from Kafka and update
 * read models in MongoDB (or any other read store).
 *
 * ## Role in CQRS
 *
 * Write side:  Command → Handler → Aggregate → Event → Outbox → Kafka
 * Read side:   Kafka → Consumer → ProjectionHandler → ReadRepository → MongoDB
 *
 * The projection handler is the bridge between the event stream and
 * the read models that power queries.
 *
 * ## Eventual consistency
 *
 * Read models are eventually consistent — there is a delay between
 * when the command is processed and when the read model is updated.
 * For most use cases (lists, detail pages), this delay is milliseconds
 * and imperceptible to users.
 *
 * ## Idempotency
 *
 * Projection handlers must be idempotent — they may receive the same
 * event more than once (Kafka at-least-once delivery).
 * Use upsert() on the read repository, not insert().
 * Upserting the same document twice produces the same result.
 *
 * ## Implementation
 *
 * Mark your projection handler with #[AsProjectionHandler]:
 *
 *   #[AsProjectionHandler(consumer: 'user.events', handlerId: 'user.read-model')]
 *   final class UserProjectionHandler implements ProjectionHandlerInterface
 *   {
 *       public function __construct(private UserReadRepository $readRepository) {}
 *
 *       public function __invoke(UserCreatedEvent $event): void
 *       {
 *           $this->readRepository->upsert((string) $event->aggregateId(), [
 *               '_id'   => (string) $event->aggregateId(),
 *               'email' => $event->email,
 *               'name'  => $event->name,
 *           ]);
 *       }
 *   }
 *
 * The #[AsProjectionHandler] attribute is discovered by the messaging module's
 * HandlerDiscoveryCompilerPass — projection handlers are wired as event handlers
 * on the specified consumer. No additional wiring needed.
 */
interface ProjectionHandlerInterface
{
    /**
     * Handle a domain event and update the read model.
     *
     * Must be idempotent — use upsert, not insert.
     * Must not throw on missing aggregates — the event may arrive
     * before the read model exists (race condition on first event).
     */
    public function __invoke(DomainEventInterface $event): void;
}
