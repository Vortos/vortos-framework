<?php

namespace Fortizan\Tekton\DependencyInjection\Compiler\Cqrs;

use Exception;
use Fortizan\Tekton\Interface\CommandHandlerInterface;
use Fortizan\Tekton\Interface\CommandInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandHandlerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('tekton.bus.command.locator')) {
            return;
        }

        $locatorDefinition = $container->findDefinition('tekton.bus.command.locator');

        $handlers = $container->findTaggedServiceIds('tekton.command.handler');

        $handlersMap = [];

        foreach ($handlers as $serviceId => $metaData) {
            $handlerDefinision = $container->getDefinition($serviceId);
         
            $method = new ReflectionMethod($handlerDefinision->getClass() . "::__invoke");
            $handlerClass = $method->getDeclaringClass();
            $handlerInterfaceNames = $handlerClass->getInterfaceNames();

            if (!in_array(CommandHandlerInterface::class, $handlerInterfaceNames)) {
                throw new Exception(
                    "Did you forgot to implement 'CommandHandlerInterface' in your " . $handlerClass->getShortName() . " class ?"
                );
            }

            $commandFqcn = $method->getParameters()[0]->getType()->getName();
            $commandClass = new ReflectionClass($commandFqcn);
            $commandInterfaceNames = $commandClass->getInterfaceNames();

            if (!in_array(CommandInterface::class, $commandInterfaceNames)) {
                throw new Exception(
                    "Did you forgot to implement 'CommandInterface' in your " . $commandClass->getShortName() . " class ?"
                );
            }

            $handlersMap[$commandFqcn] = [new Reference($serviceId)];
        }

        $locatorDefinition->replaceArgument(0, $handlersMap);
    }
}
