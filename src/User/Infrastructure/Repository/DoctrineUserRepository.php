<?php

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Fortizan\Tekton\Persistence\Contract\SourceWriterInterface;

class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private SourceWriterInterface $sourceWriter
    ) {}

    public function save(User $user): void
    {
        $this->sourceWriter->persist($user);
        $this->sourceWriter->flush();
    }

    public function getById(string $id): User
    {
        $user = $this->sourceWriter->find(User::class, $id);

        if ($user === null) {
            throw new UserNotFoundException("No user for the id {$id}");
        }

        return $user;
    }
}
