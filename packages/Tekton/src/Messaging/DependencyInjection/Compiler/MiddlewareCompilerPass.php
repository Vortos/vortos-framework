<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\DependencyInjection\Compiler;

use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MiddlewareCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // TODO: replace with MiddlewareStack::class import after Phase 8
        $middlewareStackFqcn = 'Fortizan\Tekton\Messaging\Middleware\MiddlewareStack';

        if (!$container->hasDefinition($middlewareStackFqcn)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('tekton.middleware');

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

            if (!$reflMiddleware->implementsInterface('Fortizan\Tekton\Messaging\Middleware\MiddlewareInterface')) {
                throw new LogicException(
                    "Service '{$entry['id']}' tagged 'tekton.middleware' must implement MiddlewareInterface"
                );
            }
        }

        $references = array_map(
            fn($entry) => new Reference($entry['id']),
            $middlewareEntries
        );

        $container->getDefinition($middlewareStackFqcn)->setArgument('$middlewares', $references);
    }
}
