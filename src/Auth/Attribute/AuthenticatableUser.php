<?php

declare(strict_types=1);

namespace Vortos\Auth\Attribute;

use Attribute;

/**
 * Marks a domain entity as the authenticatable user for this application.
 *
 * Place on your User aggregate. The framework discovers it at compile time
 * and generates a UserProvider automatically — no manual registration needed.
 *
 * ## Usage
 *
 *   #[AuthenticatableUser(
 *       emailField: 'email',
 *       passwordField: 'passwordHash',
 *       rolesField: 'roles',
 *   )]
 *   final class User extends AggregateRoot
 *   {
 *       // nothing changes in your entity
 *   }
 *
 * ## Field names
 *
 * emailField    — the property on your entity that holds the email address
 * passwordField — the property that holds the Argon2id hash
 * rolesField    — the property that holds the roles array
 *
 * The framework accesses these via getter methods following the convention:
 *   emailField: 'email'        → getEmail()
 *   passwordField: 'passwordHash' → getPasswordHash()
 *   rolesField: 'roles'        → getRoles()
 *
 * Or directly as public properties if no getter exists.
 *
 * ## What gets generated
 *
 * AuthDiscoveryPass generates a UserProviderInterface implementation at
 * compile time that reads from your UserRepository (also discovered automatically
 * by looking for a WriteRepositoryInterface implementation for this entity class).
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AuthenticatableUser
{
    public function __construct(
        public readonly string $emailField = 'email',
        public readonly string $passwordField = 'passwordHash',
        public readonly string $rolesField = 'roles',
    ) {}
}
