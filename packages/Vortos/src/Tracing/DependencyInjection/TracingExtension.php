<?php

declare(strict_types=1);

namespace Vortos\Tracing\DependencyInjection;

use Vortos\Tracing\Contract\TracingInterface;
use Vortos\Tracing\NoOpTracer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class TracingExtension extends Extension
{
    public function getAlias(): string
    {
        return 'vortos_tracing';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register(NoOpTracer::class, NoOpTracer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(TracingInterface::class, NoOpTracer::class)
            ->setPublic(true);
    }


}