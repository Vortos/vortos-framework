<?php

namespace Vortos\Persistence\Contract;

interface SourceReaderInterface
{
    public function fetchOne(string $query, array $params = [], array $types = []): mixed;
    public function fetchAssociative(string $query, array $params = [], array $types = []): array|false;
    public function fetchAllAssociative(string $query, array $params = [], array $types = []): array;
    public function native(): mixed;
}
