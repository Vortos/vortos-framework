<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\DependencyInjection\Compiler;

use Fortizan\Tekton\Messaging\Registry\ConsumerRegistry;
use Fortizan\Tekton\Messaging\Registry\HandlerRegistry;
use Fortizan\Tekton\Messaging\Registry\ProducerRegistry;
use Fortizan\Tekton\Messaging\Registry\TransportRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TransportRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $transports = $container->getParameter('tekton.transports');
        $producers  = $container->getParameter('tekton.producers');
        $consumers  = $container->getParameter('tekton.consumers');
        $handlers   = $container->getParameter('tekton.handlers');

        foreach ($handlers as $consumerName => $eventHandlers) {
            foreach ($eventHandlers as $eventClass => $descriptors) {
                usort($handlers[$consumerName][$eventClass], fn($a, $b) => $b['priority'] <=> $a['priority']);
            }
        }

        $container->getDefinition(TransportRegistry::class)->setArgument('$transports', $transports);
        $container->getDefinition(ProducerRegistry::class)->setArgument('$producers', $producers);
        $container->getDefinition(ConsumerRegistry::class)->setArgument('$consumers', $consumers);
        $container->getDefinition(HandlerRegistry::class)->setArgument('$handlers', $handlers);
    }
}
