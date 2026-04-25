<?php

declare(strict_types=1);

namespace Vortos\Auth\Identity;

use Vortos\Auth\Contract\UserIdentityInterface;

/**
 * Immutable authenticated user identity.
 *
 * Populated from JWT payload claims. Carries only what the JWT contains —
 * no database queries involved in construction.
 *
 * Standard JWT claims mapped:
 *   sub  → id()
 *   roles → roles()
 *
 * Stored in the request-scoped ArrayAdapter keyed 'auth:identity'
 * so any component in the same request can access it without re-parsing the token.
 */
final readonly class UserIdentity implements UserIdentityInterface
{
    /**
     * @param string   $id    User ID from JWT 'sub' claim
     * @param string[] $roles Roles from JWT 'roles' claim
     */
    public function __construct(
        private string $id,
        private array $roles = [],
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
