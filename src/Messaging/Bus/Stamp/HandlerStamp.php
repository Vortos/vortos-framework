<?php

declare(strict_types=1);

namespace Vortos\Messaging\Bus\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Records which handler processed this envelope and whether it succeeded.
 * Set by the bus after a handler is invoked. Read by LoggingMiddleware
 * and TracingMiddleware to attach handler context to logs and spans.
 */
final readonly class HandlerStamp implements StampInterface
{
    public function __construct(
        public string $handlerId,
        public bool $handled = false
    ) {}
}
