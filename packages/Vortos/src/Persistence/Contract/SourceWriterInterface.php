<?php

namespace Vortos\Persistence\Contract;

interface SourceWriterInterface
{
    public function find(string $aggregate, $id):?object;
    public function persist(object $aggregate):void;
    public function remove(object $aggregate):void;
    public function flush():void;
    public function transaction(callable $operation): mixed;
    public function native():mixed;
}