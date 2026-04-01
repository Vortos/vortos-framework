<?php

namespace Vortos\Persistence\Adapter;

use Doctrine\DBAL\Connection;
use Vortos\Persistence\Contract\SourceReaderInterface;

class DoctrineSourceReader implements SourceReaderInterface
{
    public function __construct(
        private Connection $connection
    ){
    }

    public function fetchOne(string $query, array $params = [], array $types = []): mixed
    {
        return $this->connection->fetchOne($query, $params, $types);
    }

    public function fetchAssociative(string $query, array $params = [], array $types = []): array|false
    {
        return $this->connection->fetchAssociative($query, $params, $types);
    }

    public function fetchAllAssociative(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchAllAssociative($query, $params, $types);
    }

    public function native(): mixed
    {
        return $this->connection;
    }
}