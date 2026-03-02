<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\Driver\InMemory\Definition;

use Fortizan\Tekton\Messaging\Definition\Consumer\AbstractConsumerDefinition;

/**
 * In-memory consumer definition for use in tests.
 * Paired with InMemoryTransportDefinition and InMemoryConsumer.
 */
final class InMemoryConsumerDefinition extends AbstractConsumerDefinition
{
    public function toArray(): array
    {
        return [
            'driver'      => 'in_memory',
            'transport'   => $this->transportName,
            'parallelism' => $this->parallelism,
            'batchSize'   => $this->batchSize,
            'retry'       => $this->retryPolicy,
            'dlq'         => $this->dlqTransport
        ];  
    }
}