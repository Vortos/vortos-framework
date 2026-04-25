<?php

declare(strict_types=1);

namespace Vortos\Cqrs\Attribute;

use Attribute;

/**
 * Marks a class as a projection handler.
 *
 * Projection handlers consume domain events from Kafka and update read models.
 * Discovered by HandlerDiscoveryCompilerPass — they are registered as event
 * handlers on the specified consumer, exactly like #[AsEventHandler].
 *
 * ## Usage
 *
 *   #[AsProjectionHandler(consumer: 'user.events', handlerId: 'user.read-model')]
 *   final class UserProjectionHandler
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
 * ## Idempotency requirement
 *
 * Always use upsert() — never insert().
 * Kafka delivers at-least-once. The same event may arrive twice.
 * Upserting the same document twice is safe. Inserting twice throws.
 *
 * ## Multiple events, one handler
 *
 * To handle multiple events in one projection handler, add multiple
 * #[AsProjectionHandler] attributes or create separate handler methods
 * each with their own attribute.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class AsProjectionHandler
{
    public function __construct(
        /**
         * The Kafka consumer name this projection listens to.
         * Must match a registered KafkaConsumerDefinition name.
         */
        public readonly string $consumer,

        /**
         * Unique identifier for this projection handler.
         * Used for idempotency tracking and dead letter identification.
         */
        public readonly string $handlerId,

        /**
         * Execution priority. Higher runs first.
         * Only relevant when multiple handlers process the same event on the same consumer.
         */
        public readonly int $priority = 0,
    ) {}
}
