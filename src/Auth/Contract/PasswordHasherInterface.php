<?php

declare(strict_types=1);

namespace Vortos\Auth\Contract;

/**
 * Contract for password hashing implementations.
 *
 * Implementations:
 *   ArgonPasswordHasher — default, uses PHP's password_hash with PASSWORD_ARGON2ID
 *
 * Argon2id is the current recommended algorithm for password hashing.
 * It is resistant to both time-memory trade-off attacks and side-channel attacks.
 * PHP's password_hash handles salt generation automatically.
 */
interface PasswordHasherInterface
{
    /**
     * Hash a plaintext password.
     * Returns the hash to store in the database — includes algorithm and salt.
     */
    public function hash(string $plaintext): string;

    /**
     * Verify a plaintext password against a stored hash.
     * Returns true if they match.
     */
    public function verify(string $plaintext, string $hash): bool;

    /**
     * Check if a stored hash needs to be rehashed (algorithm or cost changed).
     * If true, rehash and update the stored hash after successful login.
     */
    public function needsRehash(string $hash): bool;
}
