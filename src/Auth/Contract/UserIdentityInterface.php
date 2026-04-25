<?php

declare(strict_types=1);

namespace Vortos\Auth\Contract;

/**
 * Represents the authenticated user's identity within a request.
 *
 * This is what the framework knows about the current user. It is intentionally
 * minimal — it carries only what is needed for authorization decisions and
 * audit logging. Your domain aggregate (User) is a separate concern.
 *
 * UserIdentity is populated from the JWT payload on every request.
 * It is available via injection or via CurrentUserProvider::get().
 *
 * ## Why not inject the full User aggregate
 *
 * Injecting a full domain aggregate as "the current user" couples every
 * component to the User aggregate and forces a database round-trip on every
 * request even when the full aggregate is not needed. UserIdentity carries
 * only the fields embedded in the JWT — zero database queries for identity.
 *
 * If your handler needs the full User aggregate, load it explicitly via
 * UserRepository::findById($identity->id()).
 */
interface UserIdentityInterface
{
    /**
     * The user's unique identifier — matches the User aggregate ID.
     */
    public function id(): string;

    /**
     * The user's roles — used by the authorization layer.
     *
     * @return string[] e.g. ['ROLE_USER', 'ROLE_ADMIN']
     */
    public function roles(): array;

    /**
     * Whether the current identity is authenticated.
     * AnonymousIdentity returns false.
     */
    public function isAuthenticated(): bool;

    /**
     * Check if the identity has a specific role.
     */
    public function hasRole(string $role): bool;
}
