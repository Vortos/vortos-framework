<?php

namespace Vortos\Bus\Query\Attribute;

use Attribute;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class QueryHandler extends AsMessageHandler
{
    public function __construct(
        public ?string $bus = 'query.bus'
    )
    {
        parent::__construct(
            bus: $bus
        );
    }
}