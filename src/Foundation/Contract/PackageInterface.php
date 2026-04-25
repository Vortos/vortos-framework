<?php

declare(strict_types=1);

namespace Vortos\Foundation\Contract;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

interface PackageInterface
{
    /**
     * Return the DI Extension for this package (loads YAML/PHP configs).
     */
    public function getContainerExtension(): ?ExtensionInterface;

    /**
     * Register Compiler Passes here. This runs BEFORE the container compiles.
     */
    public function build(ContainerBuilder $container): void;
}
