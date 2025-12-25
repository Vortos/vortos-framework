<?php

namespace Fortizan\Tekton\Infrastructure\Doctrine;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Fortizan\Tekton\Domain\AggregateRootInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DomainEventDispatcher
{
    public function __construct(
        private MessageBusInterface $dispatcher
    ){
    }

    public function postPersist(PostPersistEventArgs $args):void
    {
        $this->dispatchEvents($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args):void
    {
        $this->dispatchEvents($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args):void
    {
        $this->dispatchEvents($args->getObject());
    }

    private function dispatchEvents(object $entity):void
    {
        if(!$entity instanceof AggregateRootInterface){
            return;
        }

        foreach($entity->releaseEvents() as $event){
            $this->dispatcher->dispatch($event);
        }
    }
}