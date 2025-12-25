<?php

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function getById(string $userId): User;
}