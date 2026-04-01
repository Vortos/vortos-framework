<?php

namespace Vortos\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class StringResponseListener implements EventSubscriberInterface
{
    public function onView(ViewEvent $viewEvent):void
    {
        $response = $viewEvent->getControllerResult();

        if(is_string($response)){
            $viewEvent->setResponse(new Response($response));
        }
    }

    public static function getSubscribedEvents():array
    {
        return [
            'kernel.view' => 'onView'
        ];
    }
}