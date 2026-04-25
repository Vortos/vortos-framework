<?php

declare(strict_types=1);

namespace Vortos\Messaging\Serializer;

use Vortos\Messaging\Contract\SerializerInterface;
use RuntimeException;

/**
 * Finds the correct SerializerInterface implementation for a given format string.
 * Used by producers and consumers to select serialization strategy per transport.
 * Serializers are checked in registration order — first match wins.
 */
final class SerializerLocator 
{
    public function __construct(
        /**
         * @var SerializerInterface[]
         */
        private readonly array $serializers
    ){   
    }

    public function locate(string $format): SerializerInterface
    {
        foreach($this->serializers as $serializer){
            if($serializer->supports($format)){
                return $serializer;
            }
        }

        throw new RuntimeException(
            "No serializer found for format '{$format}'. Registered formats: " . implode(', ', array_map(fn($s) => get_class($s), $this->serializers))
        );
    }
}