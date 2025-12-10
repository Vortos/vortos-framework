<?php

use App\Context\Representation\Controller\LeapYearController;
use App\Context\Representation\Controller\TestController;
use App\User\Representation\Controller\UserRegisterController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('test', new Route('/test', [
    '_controller' => [TestController::class, 'index']
]));

$routes->add('user.register', new Route('/user/register', [
    '_controller' => [UserRegisterController::class, '__invoke']
]));

$routes->add('hello', new Route('/hello/{name}', [
    'name' => 'world',
    '_controller' => function (Request $request): Response {
        $request->attributes->set('test', 'attr_test');
        $response = render_template($request);
        $response->setPublic();
        $response->setMaxAge(25);
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }
]));


$routes->add('bye', new Route('/bye', [
    '_controller' => function (Request $request): Response {
        $request->attributes->set('test', 'attr_test');
        $response = render_template($request);
        $response->setPublic();
        $response->setMaxAge(15);
        $response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
        return $response;
    }
]));

$routes->add('leap_year', new Route('/leap_year/{year}', [
    'year' => null,
    '_controller' => [LeapYearController::class, 'index']
]));

return $routes;
