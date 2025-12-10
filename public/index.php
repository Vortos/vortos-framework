<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

$routes = include __DIR__ . "/../config/routes.php";

function render_template(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include sprintf(__DIR__ . "/../src/pages/%s.php", $_route);
    return new Response(ob_get_clean());
}

$dumpFile = __DIR__ . "/container_dump.php";

if(false){    
    require_once $dumpFile;
    $container = new CachedContainer();

}else{
    $container = include __DIR__ . "/../packages/Tekton/src/Container/Container.php";
    $container->compile();
    $dumper = new PhpDumper($container);
    file_put_contents(__DIR__."/container_dump.php", $dumper->dump(['class' => 'CachedContainer']));
}

$container->set('routes', $routes); 
$framework = $container->get('framework');

$response = $framework->handle(request:$request);

$response->send();
