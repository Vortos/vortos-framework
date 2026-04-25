<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $configurator->import('./packages/*.php');
    $configurator->import('./services/*.php');

    $services->load('App\\', '../src/')
        ->exclude([
            '../src/*/Representation/View/',
            '../src/Entity/',
            '../src/test.php'
        ]);
};

// return static function (ContainerConfigurator $configurator): void {
//     $services = $configurator->services()
//         ->defaults()
//         ->autowire()
//         ->autoconfigure()
//         ->bind('$projectRoot', '%kernel.project_dir%');

//     $services->load('App\\', '../src/');

//     $services->alias(\Symfony\Component\DependencyInjection\ContainerInterface::class, 'service_container');
// };