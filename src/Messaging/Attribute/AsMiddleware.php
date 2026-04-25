<?php

declare(strict_types=1);

namespace Vortos\Messaging\Attribute;

use Attribute;

/**
 * Marks a class as a user-defined middleware to be injected into the
 * MiddlewareStack after the core middlewares.
 *
 * The priority controls order relative to other user middlewares.
 * Higher priority runs closer to the outside of the stack (runs first).
 * Core middlewares (Tracing, Logging, Hook, Transactional) always run
 * before user middlewares regardless of priority value.
 *
 * Example:
 *   #[AsMiddleware(priority: 100)]
 *   final class TenantMiddleware implements MiddlewareInterface {}
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class AsMiddleware
{
    public function __construct(
        public readonly int $priority = 0
    ) {}
}
