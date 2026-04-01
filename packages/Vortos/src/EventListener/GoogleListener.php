<?php

namespace Vortos\EventListener;

use Vortos\Http\Event\TestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GoogleListener implements EventSubscriberInterface
{
    public function onResponse(TestEvent $event): void
    {
        $response = $event->getResponse();

        if (
            $response->isRedirection()
            || ($response->headers->has('Content-Type') && !str_contains($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $event->getRequest()->getRequestFormat()
        ) {
            return;
        }

        $response->setContent($response->getContent() . " : Event Dispatcher worked correctly");
    }
    public function test(TestEvent $event): void
    {
        $response = $event->getResponse();

        if (
            $response->isRedirection()
            || ($response->headers->has('Content-Type') && !str_contains($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $event->getRequest()->getRequestFormat()
        ) {
            return;
        }

        $response->setContent($response->getContent() . " : test event listener");
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => [
                ['test', 2],
                ['onResponse', 1]
                // higher the priority number run quickly
            ],
        ];
    }
}
