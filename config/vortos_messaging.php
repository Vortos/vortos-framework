<?php

use Vortos\Messaging\DependencyInjection\VortosMessagingConfig;
use Vortos\Messaging\Driver\Kafka\Runtime\KafkaProducer;

return static function (VortosMessagingConfig $config): void {
    $config->driver()
        ->producer(KafkaProducer::class);
};