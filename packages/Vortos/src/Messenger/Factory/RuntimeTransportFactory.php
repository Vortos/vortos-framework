<?php

namespace Vortos\Messenger\Factory;

use Exception;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactory;
use Symfony\Component\Messenger\Transport\TransportInterface;

class RuntimeTransportFactory
{
    public function __construct(
        private TransportFactory $factory
    ) {}

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        if (!isset($_ENV[$dsn])) {
            throw new Exception(
                sprintf(
                    "There's no transport DSN defined in .env file with name '%s'",
                    $dsn
                )
            );
        }

        $runtimeDsn = $_ENV[$dsn] ;

        return $this->factory->createTransport($runtimeDsn, $options, $serializer);
    }
}
