<?php

use Vortos\Messenger\Transport\Kafka\Middleware\TopicResolverMiddleware;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {

        $services = $configurator->services();

        // default bus
        $services->alias(MessageBusInterface::class, 'vortos.bus.event');
        $services->alias(MessageBusInterface::class . ' $messageBus', 'vortos.bus.event');
        $services->set('vortos.bus.event', MessageBus::class)
                ->args([
                        [
                                service(TopicResolverMiddleware::class),
                                new Reference('vortos.bus.event.send_middleware'),
                                new Reference('vortos.bus.event.handle_middleware')
                        ]
                ])->tag('messenger.bus');

        $services->set(TopicResolverMiddleware::class)
                ->args([
                        []
                ]);

        $services->set('vortos.bus.event.send_middleware', SendMessageMiddleware::class)
                ->args([
                        service('messenger.sender_locator'),
                        service(EventDispatcher::class)
                ]);

        $services->set('vortos.bus.event.handle_middleware', HandleMessageMiddleware::class)
                ->args([new Reference('vortos.bus.event.locator')]);

        $services->set('vortos.bus.event.locator', HandlersLocator::class)
                ->args([[]]);


        //  command bus
        $services->alias(MessageBusInterface::class . ' $commandBus', 'vortos.bus.command');
        $services->set('vortos.bus.command', MessageBus::class)
                ->args([[new Reference('vortos.bus.command.middleware')]])
                ->tag('messenger.bus');

        $services->set('vortos.bus.command.middleware', HandleMessageMiddleware::class)
                ->args([new Reference('vortos.bus.command.locator')]);

        $services->set('vortos.bus.command.locator', HandlersLocator::class)
                ->args([[]]);


        //  query bus
        $services->alias(MessageBusInterface::class . ' $queryBus', 'vortos.bus.query');
        $services->set('vortos.bus.query', MessageBus::class)
                ->args([[new Reference('vortos.bus.query.middleware')]])
                ->tag('messenger.bus');

        $services->set('vortos.bus.query.middleware', HandleMessageMiddleware::class)
                ->args([new Reference('vortos.bus.query.locator')]);

        $services->set('vortos.bus.query.locator', HandlersLocator::class)
                ->args([[]]);
};
