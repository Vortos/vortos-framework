<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\Driver\InMemory\Definition;

use Fortizan\Tekton\Messaging\Definition\Transport\AbstractTransportDefinition;

/**
 * In-memory transport definition for use in tests.
 * No broker connection required. Messages are held in InMemoryBroker
 * for the duration of the test and cleared between tests via reset().
 */
final class InMemoryTransportDefinition extends AbstractTransportDefinition
{
    public function toArray():array
    {
        return [
            'driver' => 'in_memory',
            'name'   => $this->name,
        ];
    }
}