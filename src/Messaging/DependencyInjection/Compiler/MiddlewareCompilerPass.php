<?php

declare(strict_types=1);

namespace Vortos\Messaging\DependencyInjection\Compiler;

use Vortos\Messaging\Middleware\MiddlewareInterface;
use Vortos\Messaging\Middleware\MiddlewareStack;
use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MiddlewareCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MiddlewareStack::class)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('vortos.middleware');

        $middlewareEntries = [];

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?? 0;

                $middlewareEntries[] = ['id' => $serviceId, 'priority' => $priority];
            }
        }

        usort($middlewareEntries, fn($a, $b) => $b['priority'] <=> $a['priority']);

        foreach ($middlewareEntries as $entry) {
            $middlewareClass = $container->getDefinition($entry['id'])->getClass();

            $reflMiddleware = new ReflectionClass($middlewareClass);

            if (!$reflMiddleware->implementsInterface(MiddlewareInterface::class)) {
                throw new LogicException(
                    "Service '{$entry['id']}' tagged 'vortos.middleware' must implement MiddlewareInterface"
                );
            }
        }

        $existingDefinition = $container->getDefinition(MiddlewareStack::class);
        $existingMiddlewares = $existingDefinition->getArgument('$middlewares') ?? [];

        $references = array_map(fn($entry) => new Reference($entry['id']), $middlewareEntries);

        $container->getDefinition(MiddlewareStack::class)
            ->setArgument('$middlewares', array_merge($existingMiddlewares, $references));
    }
}
