<?php

namespace Vortos\DependencyInjection\Compiler\Projection;

use Exception;
use Vortos\Domain\Event\DomainEventInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProjectionHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('vortos.bus.event.locator')) {
            return;
        }

        $targetBus = 'vortos.bus.event';

        $locatorDefinition = $container->getDefinition('vortos.bus.event.locator');

        $handlers = $container->findTaggedServiceIds('vortos.projection.handler');

        $priorityBuckets = [];
        foreach ($handlers as $serviceId => $tags) {
            $handlerClass = $container->getReflectionClass($serviceId);

            $unsortedHandlersMap = [];
            
            foreach ($tags as $attributes) {

                if($targetBus !== $attributes['bus']){
                    continue;
                }

                if (!$handlerClass->hasMethod($attributes['method'])) {
                    throw new Exception(sprintf(
                        "Projection Method '%s' not found in class %s",
                        $attributes['method'],
                        $handlerClass->getName()
                    ));
                }

                $handlerMethod = $handlerClass->getMethod($attributes['method']);

                if ($handlerMethod->getNumberOfParameters() === 0) {
                    throw new Exception(
                        sprintf(
                            "Method %s has no arguments", 
                            $handlerMethod->getName()
                        )
                    );
                }

                $event = $handlerMethod->getParameters()[0];
                $eventFqcn = $event->getType()->getName();

                if (!is_a($eventFqcn, DomainEventInterface::class, true)) {
                    throw new Exception(
                        sprintf(
                            "Handler %s::%s accepts %s, which does not implement DomainEventInterface",
                            $handlerClass->getShortName(),
                            $handlerMethod->getName(),
                            $eventFqcn
                        )
                    );
                }

                $prio = $attributes['priority'] ?? 0;

                $priorityBuckets[$prio][] = [
                    'event' => $eventFqcn,
                    'handler' => [new Reference($serviceId), $attributes['method']] 
                ];
            }

            krsort($unsortedHandlersMap);

            // $finalHandlersMap = [];

            // foreach ($priorityBuckets as $priority => $items) {
            //     foreach ($items as $item) {
            //         $event = $item['event'];
            //         $handler = $item['handler'];

            //         $finalHandlersMap[$event][] = $handler;
            //     }
            // }
        }

        $finalHandlersMap = [];

        foreach ($priorityBuckets as $priority => $items) {
            foreach ($items as $item) {
                $event = $item['event'];
                $handler = $item['handler'];

                $finalHandlersMap[$event][] = $handler;
            }
        }

        $locatorDefinition->replaceArgument(0, $finalHandlersMap);
    }
}
