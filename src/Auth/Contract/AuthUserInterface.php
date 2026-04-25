<?php

declare(strict_types=1);

namespace Vortos\Auth\Contract;

/**
 * Minimal user data needed by the auth module.
 *
 * Your User aggregate does not implement this directly — create a thin
 * adapter class that wraps it. This keeps the auth module decoupled
 * from your domain model.
 *
 * ## Adapter example
 *
 *   final class AuthUser implements AuthUserInterface
 *   {
 *       public function __construct(private User $user) {}
 *
 *       public function getId(): string { return (string) $this->user->getId(); }
 *       public function getPasswordHash(): string { return $this->user->getPasswordHash(); }
 *       public function getRoles(): array { return $this->user->getRoles(); }
 *   }
 */
interface AuthUserInterface
{
    /** User ID as string — matches the User aggregate ID. */
    public function getId(): string;

    /** Argon2id hash stored in the database. */
    public function getPasswordHash(): string;

    /** Roles embedded in the JWT. e.g. ['ROLE_USER', 'ROLE_ADMIN'] */
    public function getRoles(): array;
}
