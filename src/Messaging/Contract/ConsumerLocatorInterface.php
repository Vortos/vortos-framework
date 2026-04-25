<?php

declare(strict_types=1);

namespace Vortos\Messaging\Contract;

/**
 * Resolves the correct ConsumerInterface implementation for a named consumer.
 *
 * Decouples ConsumerRunner and ConsumeCommand from any specific broker driver.
 * The default implementation uses KafkaConsumerFactory. Swap the binding in
 * your container to use a different driver or a test double.
 */
interface ConsumerLocatorInterface
{
    public function get(string $consumerName): ConsumerInterface;
}