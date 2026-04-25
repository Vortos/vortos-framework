<?php

declare(strict_types=1);

namespace Vortos\Auth\Jwt;

/**
 * Immutable value object representing a JWT token pair.
 *
 * Access tokens are short-lived (default 15 minutes) and used for API authentication.
 * Refresh tokens are long-lived (default 7 days) and used to obtain new access tokens.
 *
 * Both tokens are opaque strings to the caller — they are signed JWTs internally.
 *
 * ## Token rotation
 *
 * When a refresh token is used to issue a new access token, the old refresh token
 * is revoked and a new one is issued. This prevents refresh token reuse attacks.
 * TokenStorageInterface handles blacklisting of revoked tokens.
 */
final readonly class JwtToken
{
    public function __construct(
        /**
         * Short-lived JWT for API authentication.
         * Include in Authorization: Bearer {accessToken} header.
         */
        public string $accessToken,

        /**
         * Long-lived JWT for obtaining new access tokens.
         * Store securely — in httpOnly cookie, not localStorage.
         */
        public string $refreshToken,

        /**
         * Unix timestamp when the access token expires.
         */
        public int $accessTokenExpiresAt,

        /**
         * Unix timestamp when the refresh token expires.
         */
        public int $refreshTokenExpiresAt,
    ) {}

    /**
     * Serialize to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'access_token'              => $this->accessToken,
            'refresh_token'             => $this->refreshToken,
            'access_token_expires_at'   => $this->accessTokenExpiresAt,
            'refresh_token_expires_at'  => $this->refreshTokenExpiresAt,
            'token_type'                => 'Bearer',
        ];
    }
}
