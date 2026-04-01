<?php

namespace Vortos\Persistence\Adapter;

use Vortos\Persistence\Contract\ProjectionWriterInterface;
use MongoDB\Database;

class MongoProjectionWriter implements ProjectionWriterInterface
{
    public function __construct(
        private Database $db
    ) {}

    public function upsert(string $collection, string $id, array $data, array $options = []): void
    {
        $this->db->selectCollection($collection)->replaceOne(
            ['_id' => $id],
            $data,
            array_merge($options, ['upsert' => true])
        );
    }

    public function push(string $collection, string $id, string $field, mixed $value, array $options = []): void
    {
        $this->db->selectCollection($collection)->updateOne(
            ['_id' => $id],
            ['$push' => [$field => $value]],
            $options
        );
    }

    public function delete(string $collection, string $id, array $options = []): void
    {
        $this->db->selectCollection($collection)->deleteOne(
            ['_id' => $id],
            $options
        );
    }

    public function native(): mixed
    {
        return $this->db;
    }
}
