<?php

declare(strict_types=1);

namespace Vortos\Cqrs\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Vortos\Foundation\Contract\PackageInterface;
use Vortos\Cqrs\DependencyInjection\Compiler\CommandHandlerPass;
use Vortos\Cqrs\DependencyInjection\Compiler\IdempotencyKeyPass;
use Vortos\Cqrs\DependencyInjection\Compiler\QueryHandlerPass;

/**
 * CQRS package.
 *
 * Compiler pass execution order:
 *   CommandHandlerPass  (priority 50) — builds command handler map, stores as parameter
 *   QueryHandlerPass    (priority 50) — builds query handler map
 *   IdempotencyKeyPass  (priority 40) — reads command handler map, resolves strategies
 *
 * IdempotencyKeyPass must run AFTER CommandHandlerPass so the map is available.
 */
final class CqrsPackage implements PackageInterface
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CqrsExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new CommandHandlerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            50,
        );

        $container->addCompilerPass(
            new QueryHandlerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            50,
        );

        // Must run after CommandHandlerPass (priority 40 < 50)
        $container->addCompilerPass(
            new IdempotencyKeyPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            40,
        );
    }
}
