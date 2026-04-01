<?php

use Vortos\Messaging\DependencyInjection\VortosMessagingConfig;
use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryProducer;

return static function (VortosMessagingConfig $config): void {
    $config->driver()
        ->producer(InMemoryProducer::class);
};