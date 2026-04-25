<?php

declare(strict_types=1);

namespace Vortos\Domain\Command;

use Attribute;

/**
 * Marks a property as the idempotency key for this command.
 *
 * Use when a single property serves as the unique key for this command execution.
 * The compiler pass reads this at container build time — zero runtime reflection.
 *
 * Usage:
 *   final readonly class RegisterUserCommand implements CommandInterface
 *   {
 *       public function __construct(
 *           public readonly string $email,
 *           public readonly string $name,
 *           #[AsIdempotencyKey]
 *           public readonly string $requestId,
 *       ) {}
 *   }
 *
 * For complex keys involving multiple properties, override idempotencyKey()
 * directly in your command class instead of using this attribute:
 *
 *   final readonly class TransferFundsCommand implements CommandInterface
 *   {
 *       public function idempotencyKey(): ?string
 *       {
 *           return $this->fromAccountId . ':' . $this->toAccountId . ':' . $this->requestId;
 *       }
 *   }
 *
 * Commands that do not need idempotency protection: return null from idempotencyKey().
 * The command bus skips the idempotency check when null is returned.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AsIdempotencyKey {}
