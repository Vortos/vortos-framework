<?php

namespace App\User\Representation\Controller;

use Vortos\Attribute\ApiController;
use Vortos\Http\Event\TestEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/user/event', name: 'user.event')]
#[ApiController]
class CreateAnEventController {

    public function __construct(
        private EventDispatcher $dispatcher
    ){
    }

    public function __invoke(Request $request):Response
    {
        $response = new Response('hello sachintha');
        $event = $this->dispatcher->dispatch(new TestEvent($request, $response));
        return $event->getResponse();
    }
}