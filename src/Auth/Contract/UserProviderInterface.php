<?php

declare(strict_types=1);

namespace Vortos\Auth\Contract;

/**
 * Bridge between the auth module and your User aggregate.
 *
 * The auth module knows nothing about your User entity — it only knows
 * this interface. You implement it in your application layer, pointing
 * it at your UserRepository.
 *
 * ## Implementation example
 *
 *   final class AuthUserProvider implements UserProviderInterface
 *   {
 *       public function __construct(private UserRepository $repository) {}
 *
 *       public function findByEmail(string $email): ?AuthUserInterface
 *       {
 *           $user = $this->repository->findByEmail($email);
 *           return $user ? new AuthUser($user) : null;
 *       }
 *
 *       public function findById(string $id): ?AuthUserInterface
 *       {
 *           $user = $this->repository->findById(UserId::fromString($id));
 *           return $user ? new AuthUser($user) : null;
 *       }
 *
 *       public function updatePasswordHash(string $id, string $hash): void
 *       {
 *           $user = $this->repository->findById(UserId::fromString($id));
 *           $user->updatePasswordHash($hash);
 *           $this->repository->save($user);
 *       }
 *   }
 *
 * Register in config/services.php:
 *   $services->set(UserProviderInterface::class, AuthUserProvider::class);
 */
interface UserProviderInterface
{
    /**
     * Find a user by email address for login verification.
     * Return null if not found.
     */
    public function findByEmail(string $email): ?AuthUserInterface;

    /**
     * Find a user by ID for token refresh (to get fresh roles).
     * Return null if not found.
     */
    public function findById(string $id): ?AuthUserInterface;

    /**
     * Update the stored password hash after automatic rehash on login.
     * Called when ArgonPasswordHasher::needsRehash() returns true.
     */
    public function updatePasswordHash(string $id, string $hash): void;
}
