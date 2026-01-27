<?php

namespace Fortizan\Tekton\DependencyInjection\Compiler\Messenger;

use Fortizan\Tekton\Bus\Event\Attribute\AsEvent;
use Fortizan\Tekton\Messenger\Consumer;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Transport\TransportInterface;

class ConsumerTransportPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter('kernel.context') !== 'console') {
            return;
        }

        $groupId = $container->getParameter('messenger.consumer.async.group_id');

        if ($groupId === null || $groupId === '') {
            throw new \RuntimeException(
                'messenger.consumer.async.group_id must be set for console context. ' .
                    'Use $runner->setParameter("messenger.consumer.async.group_id", $groupId)'
            );
        }

        $consumerTransport = "MESSENGER_TRANSPORT_" . strtoupper(str_replace(['-', ' '], '_', $groupId)) . "_CONSUMER_DSN";

        $consumerDefinition = $container->getDefinition(Consumer::class);
        $handlersMap = $consumerDefinition->getArgument('$globalHandlerMap');

        $eventsHandledByThisGroup = [];
        foreach ($handlersMap as $groupId => $groupData) {
            foreach ($groupData as $eventClass => $handlerData) {
                $eventsHandledByThisGroup[] = $eventClass;
            }
        }

        $topicNames = [];
        foreach ($eventsHandledByThisGroup as $eventClass) {
            $reflectionEventClass = new ReflectionClass($eventClass);
            $asEventAttribute = $reflectionEventClass->getAttributes(AsEvent::class);
            $attributeArgs = $asEventAttribute[0]->getArguments();
            $topic = $attributeArgs['topic'];
            $topicNames[] = $topic;
        }

        $container->register('tekton.transport.consumer', TransportInterface::class)
            ->setFactory([new Reference('tekton.transport.factory'), 'createTransport'])
            ->setArguments([
                $consumerTransport,
                [
                    'topic' =>  $topicNames,
                    'kafka_conf' => [
                        'group.id' => $groupId,
                        'auto.offset.reset' => 'earliest'
                    ]
                ],
                new Reference('tekton.messenger.serializer')
            ]);
    }
}
