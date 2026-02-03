<?php

namespace Fortizan\Tekton\DependencyInjection\Compiler\Messenger\Consumer;

use Fortizan\Tekton\Messenger\Consumer;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConsumerHandlersMapCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(Consumer::class)) {
            return;
        }

        $handlerLocator = $container->getDefinition(Consumer::class);

        $handlerIds = $container->findTaggedServiceIds('tekton.event.handler');

        $handlersMap = [];
        foreach ($handlerIds as $id => $tags) {
            $groupId = $tags[0]['group'];
            $retries = $tags[0]['retries'];
            $delay = $tags[0]['delay'];
            $backoff = $tags[0]['backoff'];
            $dlq = $tags[0]['dlq'];
            $priority = $tags[0]['priority'] ?? 0;

            if($groupId === '' || $groupId === null){
                throw new RuntimeException(
                    sprintf("Invalid group id defined for handler class '%s'", $id)
                );
            }

            $def = $container->getDefinition($id);
            $handlerClass = $def->getClass();

            if (!$handlerClass) {
                continue;
            }

            $eventClass = $this->getEventClassFromHandlerClass($handlerClass);

            $options = [
                'retries' => $retries ?? 3,
                'delay' => $delay ?? 1000,
                'backoff' => $backoff ?? 'fixed',
                'dlq' => $dlq ?? null
            ];

            $handlersMap[$groupId][$eventClass][$priority][$id] = $options;
        }

        $sortedHandlersMap = [];
        foreach ($handlersMap as $groupId => $groupData) {
            foreach ($groupData as $eventClass => $eventData) {
                krsort($eventData);

                $flatList = [];
                foreach ($eventData as $priority => $handlerData) {
                    foreach ($handlerData as $id => $options) {
                        $flatList[$id] = $options;
                    }
                }

                $sortedHandlersMap[$groupId][$eventClass] = $flatList;
            }
        }

        $handlerLocator->setArgument('$globalHandlerMap', $sortedHandlersMap);
    }

    private function getEventClassFromHandlerClass(string $handlerClass): string
    {
        $reflectionClass = new ReflectionClass($handlerClass);
        $reflectionMethod = $reflectionClass->getMethod('__invoke');
        $eventClass = $reflectionMethod->getParameters()[0]->getType()->getName();

        return $eventClass;
    }
}
