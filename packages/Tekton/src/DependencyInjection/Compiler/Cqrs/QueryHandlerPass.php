<?php

namespace Fortizan\Tekton\DependencyInjection\Compiler\Cqrs;

use Exception;
use Fortizan\Tekton\Bus\Query\Contract\QueryInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QueryHandlerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('tekton.bus.query.locator')) {
            return;
        }

        $locatorDefinition = $container->findDefinition('tekton.bus.query.locator');

        $handlers = $container->findTaggedServiceIds('tekton.query.handler');

        $handlersMap = [];

        foreach ($handlers as $serviceId => $metaData) {
            $handlerDefinision = $container->getDefinition($serviceId);

            $handlerClass = $container->getParameterBag()->resolveValue($handlerDefinision->getClass());

            if (!class_exists($handlerClass)) {
                continue;
            }

            $handlerReflectClass = new ReflectionClass($handlerClass);

            if (!$handlerReflectClass->hasMethod('__invoke')) {
                throw new Exception(
                    sprintf(
                        "The QueryHandler class '%s' must have an '__invoke' method.",
                        $handlerReflectClass->getShortName()
                    )
                );
            }

            $handlerMethod = $handlerReflectClass->getMethod('__invoke');
            $parameters = $handlerMethod->getParameters();
            if (count($parameters) !== 1) {
                throw new Exception(
                    sprintf(
                        "'__invoke' method in your '%s' class must have exactly 1 parameter.",
                        $handlerReflectClass->getShortName()
                    )
                );
            }

            $queryType = $parameters[0]->getType();
            
            if (!$queryType instanceof \ReflectionNamedType || $queryType->isBuiltin()) {
                throw new Exception(sprintf(
                    "The argument in '__invoke' method of '%s' class must be type-hinted with a specific class.",
                    $handlerReflectClass->getShortName()
                ));
            }


            $queryFqcn = $queryType->getName();
            $queryClass = new ReflectionClass($queryFqcn);

            if (!is_subclass_of($queryFqcn, QueryInterface::class)) {
                throw new Exception(
                    sprintf(
                        "The query class '%s' (used in '%s') must implement QueryInterface.",
                        $queryClass->getShortName(),
                        $handlerReflectClass->getShortName()
                    )
                );
            }

            if (isset($handlersMap[$queryFqcn])) {
                throw new Exception(
                    sprintf("Duplicate handler for query '%s'.", $queryClass->getShortName())
                );
            }

            $handlersMap[$queryFqcn] = [new Reference($serviceId)];
        }

        $locatorDefinition->replaceArgument(0, $handlersMap);
    }
}
