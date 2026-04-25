<?php

declare(strict_types=1);

namespace Vortos\Messaging\DependencyInjection\Compiler;

use Vortos\Messaging\Registry\ConsumerRegistry;
use Vortos\Messaging\Registry\HandlerRegistry;
use Vortos\Messaging\Registry\ProducerRegistry;
use Vortos\Messaging\Registry\TransportRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TransportRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $transports = $container->getParameter('vortos.transports');
        $producers  = $container->getParameter('vortos.producers');
        $consumers  = $container->getParameter('vortos.consumers');
        $handlers   = $container->getParameter('vortos.handlers');

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
