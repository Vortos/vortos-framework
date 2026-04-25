<?php

declare(strict_types=1);

namespace Vortos\Auth\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Vortos\Auth\DependencyInjection\Compiler\AuthDiscoveryPass;
use Vortos\Foundation\Contract\PackageInterface;

/**
 * Auth package.
 *
 * Add to Container.php after CachePackage (AuthExtension uses ArrayAdapter):
 *
 *   $packages = [
 *       new CachePackage(),
 *       new AuthPackage(),
 *       new MessagingPackage(),
 *       // ...
 *   ];
 *
 * Then wrap the HTTP kernel with AuthMiddleware in Runner::run():
 *
 *   $kernel = $this->container->get('vortos');
 *   if ($this->container->has(AuthMiddleware::class)) {
 *       $kernel = $this->container->get(AuthMiddleware::class);
 *   }
 */
final class AuthPackage implements PackageInterface
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AuthExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new AuthDiscoveryPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            45, // after service autoconfiguration, before optimization
        );
    }
}
