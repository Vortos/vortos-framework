<?php

declare(strict_types=1);

namespace Vortos\Tracing\DependencyInjection;

use Vortos\Foundation\Contract\PackageInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class TracingPackage implements PackageInterface
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new TracingExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        // add compiler passes here
    }
}
