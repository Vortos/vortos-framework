<?php

declare(strict_types=1);

namespace Vortos\Messaging\Runtime;

use Vortos\Messaging\Contract\ConsumerInterface;
use Vortos\Messaging\Contract\ConsumerLocatorInterface;
use Vortos\Messaging\Driver\Kafka\Factory\KafkaConsumerFactory;

/**
 * Default ConsumerLocator implementation backed by KafkaConsumerFactory.
 *
 * Swap the ConsumerLocatorInterface binding in your container to replace
 * the entire consumer resolution strategy — for testing, alternative
 * drivers, or multi-driver routing.
 */
final class ConsumerLocator implements ConsumerLocatorInterface
{
    public function __construct(
        private KafkaConsumerFactory $factory
    ){
    }

    public function get(string $consumerName): ConsumerInterface
    {
        return $this->factory->create($consumerName);
    }
}