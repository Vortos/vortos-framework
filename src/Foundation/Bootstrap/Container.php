<?php

use Vortos\Auth\DependencyInjection\AuthPackage;
use Vortos\Authorization\DependencyInjection\AuthorizationPackage;
use Vortos\Cache\DependencyInjection\CachePackage;
use Vortos\Cqrs\DependencyInjection\CqrsPackage;
use Vortos\DependencyInjection\VortosExtension;
use Vortos\Http\DependencyInjection\HttpPackage;
use Vortos\Logger\DependencyInjection\LoggerPackage;
use Vortos\Messaging\DependencyInjection\MessagingPackage;
use Vortos\Persistence\DependencyInjection\PersistencePackage;
use Vortos\PersistenceDbal\DependencyInjection\DbalPersistencePackage;
use Vortos\PersistenceMongo\DependencyInjection\MongoPersistencePackage;
use Vortos\Tracing\DependencyInjection\TracingPackage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

$container = new ContainerBuilder();

// Global parameters
$container->setParameter('kernel.project_dir', __DIR__ . '/../../../../..');
$container->setParameter('charset', 'UTF-8');
$container->setParameter('kernel.log_path', __DIR__ . '/../../../../../var/log');



$container->register(Application::class, Application::class)
    ->setArguments(['Vortos', '1.0.0-alpha'])
    ->setPublic(true);

// Package registration — ORDER MATTERS
$packages = [
    new LoggerPackage(),        // first — everything logs
    new HttpPackage(),          // second — kernel + routing + event dispatcher
    new CachePackage(),         // before messaging and cqrs — they need CacheInterface
    new MessagingPackage(),     // before cqrs — HandlerDiscovery must run before projection discovery
    new TracingPackage(),
    new PersistencePackage(),   // before dbal and mongo adapters
    new DbalPersistencePackage(),
    new MongoPersistencePackage(),
    new CqrsPackage(),
    new AuthPackage(),
    new AuthorizationPackage(),
];

foreach ($packages as $package) {
    $package->build($container);
    $extension = $package->getContainerExtension();
    $container->registerExtension($extension);
    $container->loadFromExtension($extension->getAlias());
}


$loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../../../config'));
$loader->load('services.php');

$loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../config'));
$loader->load('services.php');

return $container;
