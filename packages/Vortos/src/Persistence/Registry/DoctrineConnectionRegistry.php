<?php

namespace Vortos\Persistence\Registry;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use Vortos\Persistence\PersistenceFactory;

class DoctrineConnectionRegistry implements ConnectionRegistry
{
    private PersistenceFactory $persistenceFactory;
    private ?Connection $connection = null;

    public function __construct(PersistenceFactory $persistenceFactory)
    {
        $this->persistenceFactory = $persistenceFactory;
    }

    public function getDefaultConnectionName(): string
    {
        return 'default';
    }

    public function getConnection(?string $name = null): Connection
    {
        if ($this->connection === null) {
   
            $sourceReader = $this->persistenceFactory->createSourceReader();
            $this->connection = $sourceReader->native();
        }

        return $this->connection;
    }

    public function getConnections(): array
    {
        return ['default' => $this->getConnection()];
    }

    public function getConnectionNames(): array
    {
        return ['default'];
    }

    // Required stubs
    public function getDefaultManagerName(): string
    {
        return 'default';
    }
    public function getManager(?string $name = null): object
    {
        throw new \BadMethodCallException('Not supported');
    }
    public function getManagers(): array
    {
        return [];
    }
    public function resetManager(?string $name = null): object
    {
        throw new \BadMethodCallException('Not supported');
    }
    public function getAliasNamespace(string $alias): string
    {
        throw new \BadMethodCallException('Not supported');
    }
    public function getManagerNames(): array
    {
        return [];
    }
    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): object
    {
        throw new \BadMethodCallException('Not supported');
    }
    public function getManagerForClass(string $class): ?object
    {
        return null;
    }
}