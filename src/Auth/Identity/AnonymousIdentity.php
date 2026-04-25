<?php

declare(strict_types=1);

namespace Vortos\Auth\Identity;

use Vortos\Auth\Contract\UserIdentityInterface;

/**
 * Represents an unauthenticated request.
 *
 * Returned by CurrentUserProvider::get() when no valid token is present.
 * Authorization checks should use isAuthenticated() before accessing id() or roles().
 *
 * Controllers marked #[RequiresAuth] never receive an AnonymousIdentity —
 * AuthMiddleware rejects the request with 401 before the controller runs.
 */
final readonly class AnonymousIdentity implements UserIdentityInterface
{
    public function id(): string
    {
        return '';
    }

    public function roles(): array
    {
        return [];
    }

    public function isAuthenticated(): bool
    {
        return false;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }
}
