<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Tracing\DependencyInjection;

use Fortizan\Tekton\Tracing\Contract\TracingInterface;
use Fortizan\Tekton\Tracing\NoOpTracer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class TracingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->register(NoOpTracer::class, NoOpTracer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(TracingInterface::class, NoOpTracer::class)
            ->setPublic(true);
    }


}