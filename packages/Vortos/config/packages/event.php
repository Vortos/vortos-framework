<?php

use Vortos\Controller\ErrorController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

return static function (ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();
    
    $services->set(ResponseListener::class, ResponseListener::class)
        ->args(['%charset%'])
        ->tag('kernel.event_subscriber');
    
    $services->set(ErrorListener::class, ErrorListener::class)
    ->args([ErrorController::class])
    ->tag('kernel.event_subscriber');
    
    $services->set(EventDispatcher::class, EventDispatcher::class);

    // $services->instanceof(EventSubscriberInterface::class)->tag('kernel.event_subscriber')->public();

    $services->load('Vortos\\EventListener\\', '../../src/EventListener');
    // loading should be the last thing, then rules will be apply to them
};
