<?php

namespace Fortizan\Tekton\Persistence\Adapter;

use Fortizan\Tekton\Persistence\Contract\ProjectionReaderInterface;
use MongoDB\Database;

class MongoProjectionReader implements ProjectionReaderInterface
{
    public function __construct(
        private Database $db
    ){
    }

    public function get(string $collection, string $id): ?array
    {
        $result = $this->db->selectCollection($collection)->findOne(['_id'=> $id]);
        return $result ? (array) $result : [];
    }

    public function filter(string $collection, array $criteria, array $options = []): array
    {
        $cursor = $this->db->selectCollection($collection)->find($criteria, $options);
        return $cursor->toArray();
    }

    public function native(): mixed
    {
        return $this->db;
    }
}