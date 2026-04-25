<?php

declare(strict_types=1);

namespace Vortos\Auth\DependencyInjection;

use Vortos\Auth\Storage\RedisTokenStorage;

/**
 * Fluent configuration for vortos-auth.
 *
 * Usage in config/auth.php:
 *
 *   return static function(VortosAuthConfig $config): void {
 *       $config
 *           ->secret(getenv('JWT_SECRET'))
 *           ->accessTokenTtl(900)
 *           ->refreshTokenTtl(604800)
 *           ->issuer(getenv('APP_NAME') ?: 'vortos');
 *   };
 *
 * Generate a secret: php -r "echo bin2hex(random_bytes(32));"
 * Add to .env: JWT_SECRET=your_generated_secret_here
 */
final class VortosAuthConfig
{
    private string $secret = '';
    private int $accessTokenTtl = 900;
    private int $refreshTokenTtl = 604800;
    private string $issuer = 'vortos';
    private string $tokenStorage = RedisTokenStorage::class;
    private bool $enableBuiltInControllers = false;

    public function secret(string $secret): static
    {
        $this->secret = $secret;
        return $this;
    }

    public function accessTokenTtl(int $seconds): static
    {
        $this->accessTokenTtl = $seconds;
        return $this;
    }

    public function refreshTokenTtl(int $seconds): static
    {
        $this->refreshTokenTtl = $seconds;
        return $this;
    }

    public function issuer(string $issuer): static
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * Swap the token storage implementation.
     * @param class-string<\Vortos\Auth\Contract\TokenStorageInterface> $storageClass
     */
    public function tokenStorage(string $storageClass): static
    {
        $this->tokenStorage = $storageClass;
        return $this;
    }

    public function enableBuiltInControllers(bool $enable = true): static
    {
        $this->enableBuiltInControllers = $enable;
        return $this;
    }

    /** @internal */
    public function toArray(): array
    {
        return [
            'secret'           => $this->secret,
            'access_token_ttl' => $this->accessTokenTtl,
            'refresh_token_ttl' => $this->refreshTokenTtl,
            'issuer'           => $this->issuer,
            'token_storage'    => $this->tokenStorage,
            'enable_built_in_controllers' => $this->enableBuiltInControllers,
        ];
    }
}
