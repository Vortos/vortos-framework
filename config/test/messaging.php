<?php

use Vortos\Messaging\DependencyInjection\VortosMessagingConfig;
use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryProducer;
use Vortos\Messaging\Driver\InMemory\Runtime\InMemoryConsumer;

return static function (VortosMessagingConfig $config): void {
    $config->driver()
        ->producer(InMemoryProducer::class)
        ->consumer(InMemoryConsumer::class);
};
