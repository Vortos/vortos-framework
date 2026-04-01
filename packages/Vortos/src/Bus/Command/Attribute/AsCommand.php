<?php

namespace Vortos\Bus\Command\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsCommand
{
    public function __construct(
        public  string $transport = 'async',
        public string $topic = 'default_topic'
    ) {}
}
