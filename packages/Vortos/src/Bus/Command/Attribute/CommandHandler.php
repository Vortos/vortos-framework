<?php

namespace Vortos\Bus\Command\Attribute;

use Attribute;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class CommandHandler extends AsMessageHandler
{
    public function __construct(
        public ?string $bus = 'vortos.bus.command',
        public ?string $fromTransport = null
    ) {
        parent::__construct(
            bus: $bus,
            fromTransport: $fromTransport
        );
    }
}
