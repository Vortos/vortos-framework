<?php

namespace Fortizan\Tekton\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use MongoDB\Client as MongoClient;
use MongoDB\Database as MongoDatabase;

class DatabaseFactory
{
    public function createEntityManager(array $connectionParams, array $entityPaths, bool $isDevMode): EntityManager
    {
        [$configuration, $connection] = $this->getConfigs($connectionParams, $entityPaths, $isDevMode);

        $entityManager = new EntityManager($connection, $configuration);

        return $entityManager;
    }

    public function createConnection(array $connectionParams, array $entityPaths, bool $isDevMode): Connection
    {
        [, $connection] = $this->getConfigs($connectionParams, $entityPaths, $isDevMode);

        return $connection;
    }

    private function getConfigs(array $connectionParams, array $entityPaths, bool $isDevMode): array
    {
        $configuration = ORMSetup::createAttributeMetadataConfig($entityPaths, $isDevMode);

        $proxyDir = dirname(__DIR__, 2) . '/var/doctrine/proxies';
        if (!is_dir($proxyDir)) {
            @mkdir($proxyDir, 0775, true);
        }

        $configuration->setProxyDir($proxyDir);
        $configuration->setProxyNamespace('DoctrineProxies');
        $configuration->setAutoGenerateProxyClasses($isDevMode ? 1 : 0);

        $connection = DriverManager::getConnection($connectionParams, $configuration);

        return [$configuration, $connection];
    }

    public function createMongoConnection(): MongoDatabase
    {
        $uri = sprintf(
            "mongodb://%s:%s@%s:%s",
            $_ENV['MONGO_INITDB_ROOT_USERNAME'],
            $_ENV['MONGO_INITDB_ROOT_PASSWORD'],
            $_ENV['MONGO_HOST'],
            $_ENV['MONGO_PORT']
        );

        $client = new MongoClient($uri);

        return $client->selectDatabase($_ENV['MONGO_DB_NAME']);
    }
}
