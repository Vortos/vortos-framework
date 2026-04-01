<?php

declare(strict_types=1);

namespace Vortos\Messaging\Bus\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries the distributed trace correlation ID through the Symfony Messenger envelope.
 * Injected into handler parameters marked with #[CorrelationId].
 * Propagated from incoming message headers or generated fresh by EventBus if absent.
 * Chain this value through all downstream events and log entries.
 */
final readonly class CorrelationIdStamp implements StampInterface
{
    public function __construct(
        public string $correlationId
    ) {}
}
