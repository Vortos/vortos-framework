<?php

declare(strict_types=1);

namespace Vortos\Auth\Provider;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Vortos\Auth\Contract\AuthUserInterface;
use Vortos\Auth\Contract\UserProviderInterface;
use Vortos\Domain\Repository\WriteRepositoryInterface;

/**
 * Automatically generated UserProvider.
 *
 * Created by AuthDiscoveryPass when #[AuthenticatableUser] is found on an entity.
 * Uses the field names from the attribute to read email, password hash, and roles
 * from your entity via getter methods or public properties.
 *
 * You never instantiate this directly — the container builds it.
 */
// #[Autoconfigure(autowire: false)] 
final class ReflectiveUserProvider implements UserProviderInterface
{
    public function __construct(
        private WriteRepositoryInterface $repository,
        private string $entityClass,
        private string $emailField,
        private string $passwordField,
        private string $rolesField,
    ) {}

    public function findByEmail(string $email): ?AuthUserInterface
    {
        // Repository must have findByEmail — checked at compile time by AuthDiscoveryPass
        if (!method_exists($this->repository, 'findByEmail')) {
            throw new \LogicException(sprintf(
                'Repository "%s" must have a findByEmail(string $email) method '
                    . 'to use #[AuthenticatableUser] built-in controllers.',
                get_class($this->repository),
            ));
        }

        $entity = $this->repository->findByEmail($email);
        return $entity ? new ReflectiveAuthUser($entity, $this->passwordField, $this->rolesField) : null;
    }

    public function findById(string $id): ?AuthUserInterface
    {
        // AggregateId class is inferred from entity — look for getId() return type
        $reflection = new \ReflectionClass($this->entityClass);
        $idMethod = $reflection->getMethod('getId');
        $returnType = $idMethod->getReturnType();

        if (!$returnType instanceof \ReflectionNamedType) {
            throw new \LogicException('getId() must have a return type.');
        }

        $idClass = $returnType->getName();
        $aggregateId = $idClass::fromString($id);
        $entity = $this->repository->findById($aggregateId);

        return $entity ? new ReflectiveAuthUser($entity, $this->passwordField, $this->rolesField) : null;
    }

    public function updatePasswordHash(string $id, string $hash): void
    {
        $reflection = new \ReflectionClass($this->entityClass);
        $idMethod = $reflection->getMethod('getId');
        $returnType = $idMethod->getReturnType();
        $idClass = $returnType->getName();

        $entity = $this->repository->findById($idClass::fromString($id));

        if ($entity === null) {
            return;
        }

        $setter = 'set' . ucfirst($this->passwordField);

        if (method_exists($entity, $setter)) {
            $entity->$setter($hash);
        } else {
            $reflection->getProperty($this->passwordField)->setValue($entity, $hash);
        }

        $this->repository->save($entity);
    }
}
