<?php

declare(strict_types=1);

namespace Vortos\Messaging\Driver\InMemory\Definition;

use Vortos\Messaging\Definition\Producer\AbstractProducerDefinition;

/**
 * In-memory producer definition for use in tests.
 * Paired with InMemoryTransportDefinition and InMemoryProducer.
 */
final class InMemoryProducerDefinition extends AbstractProducerDefinition
{
    public function toArray():array
    {
        return [
            'driver'    => 'in_memory',
            'transport' => $this->transportName,
            'outbox'    => [
                'enabled' => $this->outboxEnabled,
                'table'   => $this->outboxTable,
            ],
            'headers'   => $this->headers
        ];
    }


}