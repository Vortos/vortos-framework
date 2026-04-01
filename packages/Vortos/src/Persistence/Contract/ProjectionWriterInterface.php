<?php

namespace Vortos\Persistence\Contract;

interface ProjectionWriterInterface
{
    public function upsert(string $collection, string $id, array $data, array $options = []):void;
    public function push(string $collection, string $id, string $field, mixed $value, array $options = []):void;
    public function delete(string $collection, string $id, array $options = []):void;
    public function native():mixed;
}