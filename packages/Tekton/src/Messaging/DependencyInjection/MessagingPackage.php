<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\DependencyInjection;

use Fortizan\Tekton\Container\Contract\PackageInterface;
use Fortizan\Tekton\Messaging\DependencyInjection\Compiler\HandlerDiscoveryCompilerPass;
use Fortizan\Tekton\Messaging\DependencyInjection\Compiler\HookDiscoveryCompilerPass;
use Fortizan\Tekton\Messaging\DependencyInjection\Compiler\MessagingConfigCompilerPass;
use Fortizan\Tekton\Messaging\DependencyInjection\Compiler\MiddlewareCompilerPass;
use Fortizan\Tekton\Messaging\DependencyInjection\Compiler\TransportRegistryCompilerPass;
use Fortizan\Tekton\Messaging\DependencyInjection\MessagingExtension;
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
