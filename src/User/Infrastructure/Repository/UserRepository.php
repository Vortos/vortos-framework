<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Vortos\Domain\Aggregate\AggregateRoot;
use Vortos\Domain\Identity\AggregateId;
use Vortos\PersistenceDbal\Write\PostgresWriteRepository;

final class UserRepository extends PostgresWriteRepository
{
    protected function tableName(): string
    {
        return 'users';
    }

    protected function columnMap(): array
    {
        return [
            'id'            => Types::STRING,
            'name'          => Types::STRING,
            'email'         => Types::STRING,
            'password_hash' => Types::STRING,
            'roles'         => Types::JSON,
            'version'       => Types::INTEGER,
        ];
    }

    protected function toRow(AggregateRoot $aggregate): array
    {
        /** @var User $aggregate */
        return [
            'id'            => (string) $aggregate->getId(),
            'name'          => $aggregate->getName(),
            'email'         => $aggregate->getEmail(),
            'password_hash' => $aggregate->getPasswordHash(),
            'roles'         => $aggregate->getRoles(),
            'version'       => $aggregate->getVersion(),
        ];
    }

    protected function fromRow(array $row): AggregateRoot
    {
        return User::reconstruct(
            UserId::fromString($row['id']),
            $row['name'],
            $row['email'],
            $row['password_hash'],
            is_array($row['roles']) ? $row['roles'] : json_decode($row['roles'], true),
            (int) $row['version'],
        );
    }

    /**
     * Find user by email address.
     * Used by AuthDiscoveryPass-generated UserProvider for login.
     */
    public function findByEmail(string $email): ?User
    {
        $row = $this->connection()->createQueryBuilder()
            ->select('*')
            ->from($this->tableName())
            ->where('email = :email')
            ->setParameter('email', $email)
            ->executeQuery()
            ->fetchAssociative();

        return $row !== false ? $this->fromRow($row) : null;
    }
}
