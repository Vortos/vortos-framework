<?php

namespace App\User\Infrastructure\Query;

use MongoDB\Database as MongoDatabase;

class MongoUserFinder
{
    public function __construct(
        private MongoDatabase $db
    ){
    }

    public function findById(int $id): array
    {
        return (array) $this->db->user->findOne(['_id' => $id]);
    }
}