<?php

declare(strict_types=1);

namespace Vortos\Messaging\Bus\Stamp;

use DateTimeImmutable;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries the event occurrence timestamp through the Symfony Messenger envelope.
 * Injected into handler parameters marked with #[Timestamp].
 * Set by EventBus at dispatch time to the current time.
 */
final readonly class TimestampStamp implements StampInterface
{
    public function __construct(
        public DateTimeImmutable $occurredAt
    ) {}
}
