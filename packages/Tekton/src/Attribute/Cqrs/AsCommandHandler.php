<?php

namespace Fortizan\Tekton\Attribute\Cqrs;

use Attribute;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsCommandHandler extends AsMessageHandler
{
    public function __construct(
        // public ?string $bus = null,
        public ?string $bus = 'messenger.bus.command',
        public ?string $fromTransport = null,
        public ?string $handles = null,
        public ?string $method = null,
        public int $priority = 0
    ) {
        parent::__construct(
            bus: $bus,
            fromTransport: $fromTransport,
            handles: $handles,
            method: $method,
            priority: $priority
        );
    }
}
