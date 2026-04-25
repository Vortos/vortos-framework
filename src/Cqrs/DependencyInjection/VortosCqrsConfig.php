<?php

declare(strict_types=1);

namespace Vortos\Cqrs\DependencyInjection;

use Vortos\Cqrs\Command\Idempotency\RedisCommandIdempotencyStore;

/**
 * Fluent configuration object for vortos-cqrs.
 *
 * Used in config/cqrs.php:
 *
 *   return static function(VortosCqrsConfig $config): void {
 *       $config->commandBus()
 *           ->idempotencyStore(RedisCommandIdempotencyStore::class)
 *           ->idempotencyTtl(86400);
 *   };
 *
 * Sensible defaults ship out of the box — no config required for standard usage.
 */
final class VortosCqrsConfig
{
    private string $idempotencyStore = RedisCommandIdempotencyStore::class;
    private int $idempotencyTtl = 86400;
    private bool $strictIdempotency = false;

    /**
     * Get the command bus config builder.
     * Returns $this for fluent chaining.
     */
    public function commandBus(): static
    {
        return $this;
    }

    /**
     * Set the idempotency store implementation.
     *
     * @param class-string<CommandIdempotencyStoreInterface> $storeClass
     */
    public function idempotencyStore(string $storeClass): static
    {
        $this->idempotencyStore = $storeClass;
        return $this;
    }

    /**
     * Set idempotency key TTL in seconds.
     * Default: 86400 (24 hours).
     */
    public function idempotencyTtl(int $ttl): static
    {
        $this->idempotencyTtl = $ttl;
        return $this;
    }

    /**
     * Enable strict idempotency mode.
     * In strict mode, DuplicateCommandException is thrown for duplicate commands.
     * In lenient mode (default), duplicates are silently skipped.
     */
    public function strictIdempotency(bool $strict = true): static
    {
        $this->strictIdempotency = $strict;
        return $this;
    }

    /** @internal */
    public function toArray(): array
    {
        return [
            'command_bus' => [
                'idempotency_store' => $this->idempotencyStore,
                'idempotency_ttl'   => $this->idempotencyTtl,
                'strict_idempotency' => $this->strictIdempotency,
            ],
        ];
    }
}
