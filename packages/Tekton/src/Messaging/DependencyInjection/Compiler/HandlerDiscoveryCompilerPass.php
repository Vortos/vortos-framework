<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messaging\DependencyInjection\Compiler;

use Fortizan\Tekton\Messaging\Attribute\AsEventHandler;
use Fortizan\Tekton\Messaging\Attribute\Header\CorrelationId;
use Fortizan\Tekton\Messaging\Attribute\Header\MessageId;
use Fortizan\Tekton\Messaging\Attribute\Header\Timestamp;
use Fortizan\Tekton\Messaging\Contract\DomainEventInterface;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class HandlerDiscoveryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container):void
    {
        if(!$container->hasParameter('tekton.handlers')){
            $container->setParameter('tekton.handlers', []);
        }

        $taggedServices = $container->findTaggedServiceIds('tekton.event_handler');

        foreach($taggedServices as $serviceId => $tags){
            $containerDefinition = $container->getDefinition($serviceId);
            $className = $containerDefinition->getClass();

            $reflClass = new ReflectionClass($className);

            $this->processHandlerClass($container, $serviceId, $reflClass);
        }
    }

    private function processHandlerClass(ContainerBuilder $container, string $serviceId, ReflectionClass $reflClass):void
    {
        $classAttrs = $reflClass->getAttributes(AsEventHandler::class);

        if(!empty($classAttrs)){
            $attribute = $classAttrs[0]->newInstance();

            if(!$reflClass->hasMethod('__invoke')){
                throw new LogicException(
                    "Class '{$reflClass->getName()}' has #[AsEventHandler] but no __invoke method"
                );
            }

            $invokeMethod = $reflClass->getMethod('__invoke');

            $this->buildAndStoreDescriptor($container, $serviceId, $invokeMethod, $attribute);
        }

        $methods = $reflClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method){

            if (!empty($classAttrs) && $method->getName() === '__invoke') {
                continue;
            }
            
            $methodAttrs = $method->getAttributes(AsEventHandler::class);

            foreach($methodAttrs as $attrRefl){
                $attribute = $attrRefl->newInstance();

                $this->buildAndStoreDescriptor($container, $serviceId, $method, $attribute);
            }
        }
    }

    private function buildAndStoreDescriptor(
        ContainerBuilder $container, 
        string $serviceId, 
        ReflectionMethod $method, 
        AsEventHandler $attribute
    ):void
    {
        $eventClass = $this->resolveEventClass($method);

        $descriptor = [
            'handlerId' => $attribute->handlerId, 
            'serviceId' => $serviceId, 
            'method' => $method->getName(), 
            'priority' => $attribute->priority, 
            'idempotent' => $attribute->idempotent, 
            'version' => $attribute->version, 
            'eventClass' =>  $eventClass
        ];

        $handlers = $container->getParameter('tekton.handlers');
        $handlers[$attribute->consumer][$eventClass][] = $descriptor;

        $container->setParameter('tekton.handlers', $handlers);
    }

    private function resolveEventClass(ReflectionMethod $method): string
    {
        foreach($method->getParameters() as $param){

            if(!empty($param->getAttributes(CorrelationId::class)) || !empty($param->getAttributes(Timestamp::class)) || !empty($param->getAttributes(MessageId::class))){
                continue;    
            }

            $type = $param->getType();

            if(!$type instanceof ReflectionNamedType){
                continue;
            }

            if($type->isBuiltin()){
                continue;
            }

            $typeName = $type->getName();

            if(!class_exists($typeName)){
                throw new LogicException(
                    "Parameter type '{$typeName}' in handler '{$method->getDeclaringClass()->getName()}::{$method->getName()}' does not exist"
                );
            }

            $reflEventClass = new ReflectionClass($typeName);

            if(!$reflEventClass->implementsInterface(DomainEventInterface::class)){
                throw new LogicException(
                    "Parameter '{$typeName}' in handler '{$method->getDeclaringClass()->getName()}::{$method->getName()}' must implement DomainEventInterface"
                );
            }

            return $typeName;
        }

        throw new LogicException(
            "Handler '{$method->getDeclaringClass()->getName()}::{$method->getName()}' has no parameter implementing DomainEventInterface"
        );
    }
}