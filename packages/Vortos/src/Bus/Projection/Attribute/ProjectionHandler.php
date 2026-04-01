<?php

namespace Vortos\Bus\Projection\Attribute;

use Attribute;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ProjectionHandler extends AsMessageHandler
{
    public function __construct(
        public ?string $bus = 'vortos.bus.event',
        public ?string $fromTransport = null, //kafka later
        public int $priority = 0 
    ) {
        parent::__construct(
            bus: $bus,
            fromTransport: $fromTransport,
            priority: $priority
        );
    }
}
