<?php

namespace Vortos\DependencyInjection\Compiler\Cqrs;

use Exception;
use Vortos\Bus\Command\Contract\CommandInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandHandlerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('vortos.bus.command.locator')) {
            return;
        }

        $locatorDefinition = $container->findDefinition('vortos.bus.command.locator');

        $handlers = $container->findTaggedServiceIds('vortos.command.handler');

        $handlersMap = [];

        foreach ($handlers as $serviceId => $attributes) {
            $handlerDefinision = $container->getDefinition($serviceId);

            $handlerClass = $container->getParameterBag()->resolveValue($handlerDefinision->getClass());

            if (!class_exists($handlerClass)) {
                continue;
            }

            $handlerReflectClass = new ReflectionClass($handlerClass);

            if (!$handlerReflectClass->hasMethod('__invoke')) {
                throw new Exception(
                    sprintf(
                        "The CommandHandler class '%s' must have an '__invoke' method.",
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

            $commandType = $parameters[0]->getType();

            if (!$commandType instanceof \ReflectionNamedType || $commandType->isBuiltin()) {
                throw new Exception(sprintf(
                    "The argument in '__invoke' method of '%s' class must be type-hinted with a specific class.",
                    $handlerReflectClass->getShortName()
                ));
            }


            $commandFqcn = $commandType->getName();
            $commandClass = new ReflectionClass($commandFqcn);

            if (!is_subclass_of($commandFqcn, CommandInterface::class)) {
                throw new Exception(
                    sprintf(
                        "The command class '%s' (used in '%s') must implement CommandInterface.",
                        $commandClass->getShortName(),
                        $handlerReflectClass->getShortName()
                    )
                );
            }


            if (isset($handlersMap[$commandFqcn])) {
                throw new Exception(
                    sprintf("Duplicate handler for command '%s'.", $commandClass->getShortName())
                );
            }

            $handlersMap[$commandFqcn] = [new Reference($serviceId)];
        }

        $locatorDefinition->replaceArgument(0, $handlersMap);
    }
}
