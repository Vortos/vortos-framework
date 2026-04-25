<?php

declare(strict_types=1);

namespace Vortos\Auth\Provider;

use Vortos\Auth\Contract\AuthUserInterface;

/**
 * Wraps any entity as AuthUserInterface using reflection to read fields.
 *
 * Tries getter methods first (getEmail(), getPasswordHash(), getRoles()),
 * falls back to direct property access for public properties.
 */
final class ReflectiveAuthUser implements AuthUserInterface
{
    public function __construct(
        private object $entity,
        private string $passwordField,
        private string $rolesField,
    ) {}

    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    public function getPasswordHash(): string
    {
        return (string) $this->readField($this->passwordField);
    }

    public function getRoles(): array
    {
        $roles = $this->readField($this->rolesField);
        return is_array($roles) ? $roles : [$roles];
    }

    private function readField(string $field): mixed
    {
        $getter = 'get' . ucfirst($field);

        if (method_exists($this->entity, $getter)) {
            return $this->entity->$getter();
        }

        $reflection = new \ReflectionClass($this->entity);
        $property = $reflection->getProperty($field);
        $property->setAccessible(true);
        return $property->getValue($this->entity);
    }
}
