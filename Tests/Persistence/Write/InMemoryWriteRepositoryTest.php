<?php

declare(strict_types=1);

namespace Vortos\Tests\Persistence\Write;

use PHPUnit\Framework\TestCase;
use App\User\Domain\Entity\User;
use Vortos\Persistence\Write\InMemoryWriteRepository;
use Vortos\Domain\Repository\Exception\OptimisticLockException;

final class InMemoryWriteRepositoryTest extends TestCase
{
    public function test_it_throws_optimistic_lock_exception_on_stale_update(): void
    {
        $repository = new class extends InMemoryWriteRepository {};

        // Assuming a newly registered user starts at version 0
        $userA = User::registerUser('John Doe', 'john@example.com');

        // Save increments both the live object and the stored clone
        $repository->save($userA); // store now has v1, userA is now v1

        // Simulate a second process loading the same user
        $staleUser = $repository->findById($userA->getId()); // staleUser is a clone at v1

        // The first process updates and saves
        $userA->setName('Jane Doe');
        $repository->save($userA); // store now has v2, userA is now v2

        // The second process tries to save its stale copy
        $this->expectException(OptimisticLockException::class);
        $staleUser->setName('Johnny');

        // Conflict! The store is at v2, but staleUser expects the store to be at v1.
        $repository->save($staleUser);
    }
}
