<?php

declare(strict_types=1);

namespace Vortos\Messaging\DeadLetter;

use Doctrine\DBAL\Connection;

final class DeadLetterRepository
{
    public function __construct(
        private Connection $connection,
        private string $table = 'vortos_failed_messages'
    ) {}

    public function fetchFailed(int $limit = 50): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->table}
             WHERE status = 'failed'
             ORDER BY failed_at ASC
             LIMIT :limit",
            ['limit' => $limit],
            ['limit' => \Doctrine\DBAL\ParameterType::INTEGER]
        );
    }

    public function markReplayed(string $id): void
    {
        $this->connection->update($this->table, [
            'status'      => 'replayed',
            'replayed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }
}
