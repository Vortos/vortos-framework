<?php

declare(strict_types=1);

namespace Vortos\Messaging\Bus\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Marker stamp indicating this event should be routed through the transactional outbox.
 * When present on an envelope, EventBus calls OutboxInterface::store() instead of
 * producing directly to the broker. The OutboxRelayWorker handles actual broker production.
 */
final readonly class OutboxStamp implements StampInterface
{
}
