<?php

declare(strict_types=1);

namespace Vortos\Messaging\DependencyInjection;

use Vortos\Container\Contract\PackageInterface;
use Vortos\Messaging\DependencyInjection\Compiler\HandlerDiscoveryCompilerPass;
use Vortos\Messaging\DependencyInjection\Compiler\HookDiscoveryCompilerPass;
use Vortos\Messaging\DependencyInjection\Compiler\MessagingConfigCompilerPass;
use Vortos\Messaging\DependencyInjection\Compiler\MiddlewareCompilerPass;
use Vortos\Messaging\DependencyInjection\Compiler\TransportRegistryCompilerPass;
use Vortos\Messaging\DependencyInjection\MessagingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class MessagingPackage implements PackageInterface
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MessagingExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MessagingConfigCompilerPass());
        $container->addCompilerPass(new HandlerDiscoveryCompilerPass());
        $container->addCompilerPass(new TransportRegistryCompilerPass());
        $container->addCompilerPass(new MiddlewareCompilerPass());
        $container->addCompilerPass(new HookDiscoveryCompilerPass());
    }
}
