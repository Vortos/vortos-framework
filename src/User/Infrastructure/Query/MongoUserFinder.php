<?php

namespace App\User\Infrastructure\Query;

use App\User\Application\Query\Contract\UserFinderInterface;
use Vortos\Persistence\Contract\ProjectionReaderInterface;

class MongoUserFinder implements UserFinderInterface
{
    private const COLLECTION = 'users';

    public function __construct(
        private ProjectionReaderInterface $projectionReader
    ) {}

    public function findById(string $id): ?array
    {
        $user = $this->projectionReader->get(self::COLLECTION, $id);

        return $user;
    }

    public function findByEmail(string $email): array
    {
        $user = $this->projectionReader->filter(self::COLLECTION, ['email' => $email]);

        return $user;
    }
}
