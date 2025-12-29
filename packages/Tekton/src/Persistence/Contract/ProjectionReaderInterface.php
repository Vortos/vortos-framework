<?php

namespace Fortizan\Tekton\Persistence\Contract;

interface ProjectionReaderInterface
{
    public function get(string $collection, string $id):?array;

    public function filter(string $collection, array $criteria, array $options = []):array;

    public function native():mixed;
}