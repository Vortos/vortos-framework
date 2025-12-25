<?php

namespace Fortizan\Tekton\Persistence\Adapter;

use Doctrine\ORM\EntityManagerInterface;
use Fortizan\Tekton\Persistence\Contract\SourceWriterInterface;

class DoctrineSourceWriter implements SourceWriterInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){
    }

    public function persist(object $aggregate):void
    {
        $this->em->persist($aggregate);
    }

    public function remove(object $aggregate):void
    {
        $this->em->remove($aggregate);
    }

    public function flush():void
    {
        $this->em->flush();
    }

    public function transaction(callable $operation): mixed
    {
        return $this->em->wrapInTransaction($operation);   
    }

    public function native(): mixed
    {
        return $this->em;
    }
}