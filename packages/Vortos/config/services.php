<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Vortos\Controller\ErrorController;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$projectRoot', '%kernel.project_dir%');

    // Load application code
    $services->load('App\\', '../src/');

    // Load Vortos framework source (packages loaded via Container.php modules)
    $services->load('Vortos\\', '../packages/Vortos/src/')
        ->exclude([
            '../packages/Vortos/src/Container/Container.php',
            '../packages/Vortos/src/Http/Kernel.php',
            '../packages/Vortos/src/EventListener/',
            '../packages/Vortos/src/Auth/Provider/',
            // DI extension classes — not services
            '../packages/Vortos/src/*/DependencyInjection/',
            '../packages/Vortos/src/Messaging/DependencyInjection/',
            '../packages/Vortos/src/Persistence/DependencyInjection/',
            '../packages/Vortos/src/PersistenceDbal/DependencyInjection/',
            '../packages/Vortos/src/PersistenceMongo/DependencyInjection/',
            '../packages/Vortos/src/Cqrs/DependencyInjection/',
            '../packages/Vortos/src/Cache/DependencyInjection/',
            '../packages/Vortos/src/Auth/DependencyInjection/',
            '../packages/Vortos/src/Authorization/DependencyInjection/',
            '../packages/Vortos/src/Http/DependencyInjection/',
            '../packages/Vortos/src/Logger/DependencyInjection/',
        ]);

    $services->get(ErrorController::class)
        ->arg('$debug', '%kernel.debug%')
        ->public();

    $services->alias(ContainerInterface::class, 'service_container');
};
