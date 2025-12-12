<?php

use Fortizan\Tekton\Routing\RouteAttributeClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;

return function (ContainerBuilder $container) {

    $classLoader = new RouteAttributeClassLoader();

    $controllerIds = $container->findTaggedServiceIds('tekton.api.controller');

    $routes = new RouteCollection();

    foreach ($controllerIds as $id => $t) {

        $class = $container->getDefinition($id)->getClass();

        $routes->addCollection($classLoader->load($class));
    }

    return $routes;
};
