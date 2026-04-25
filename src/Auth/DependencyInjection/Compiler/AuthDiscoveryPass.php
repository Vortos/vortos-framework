<?php

declare(strict_types=1);

namespace Vortos\Auth\DependencyInjection\Compiler;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Vortos\Auth\Attribute\AuthenticatableUser;
use Vortos\Auth\Contract\UserProviderInterface;
use Vortos\Auth\Provider\ReflectiveUserProvider;

/**
 * Discovers #[AuthenticatableUser] on entity classes and automatically
 * registers a UserProviderInterface implementation.
 *
 * ## Discovery process
 *
 * 1. Scans all registered services for classes with #[AuthenticatableUser]
 * 2. Finds the corresponding repository (service implementing WriteRepositoryInterface
 *    whose findById() return type matches the entity class)
 * 3. Registers ReflectiveUserProvider with the entity class, field names, and repository
 * 4. Aliases UserProviderInterface to ReflectiveUserProvider
 *
 * ## What this eliminates
 *
 * Without this pass, users need:
 *   - AuthUserInterface implementation
 *   - UserProviderInterface implementation
 *   - Manual service registration
 *
 * With this pass, users need:
 *   - #[AuthenticatableUser] on their entity (one line)
 *
 * ## Fallback
 *
 * If UserProviderInterface is already manually registered (custom auth),
 * this pass skips — never overwrites manual registrations.
 */
final class AuthDiscoveryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // If user already registered their own UserProviderInterface — skip
        if (
            $container->hasAlias(UserProviderInterface::class)
            || $container->hasDefinition(UserProviderInterface::class)
        ) {
            return;
        }

        $entityClass = null;
        $authAttr = null;

        // Scan all service definitions for #[AuthenticatableUser]
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            $class = $definition->getClass();

            if ($class === null || !class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attrs = $reflection->getAttributes(AuthenticatableUser::class);

            if (!empty($attrs)) {
                $entityClass = $class;
                $authAttr = $attrs[0]->newInstance();
                break;
            }
        }

        if ($entityClass === null || $authAttr === null) {
            return; // No #[AuthenticatableUser] found — user handles auth manually
        }

        // Find the repository for this entity
        $repositoryServiceId = $this->findRepository($container, $entityClass);

        if ($repositoryServiceId === null) {
            throw new \LogicException(sprintf(
                'Found #[AuthenticatableUser] on "%s" but could not find a registered '
                    . 'repository service for it. Make sure your UserRepository is registered '
                    . 'as a service and implements WriteRepositoryInterface.',
                $entityClass,
            ));
        }

        // Register ReflectiveUserProvider
        $definition = new Definition(ReflectiveUserProvider::class);
        $definition->setArguments([
            new Reference($repositoryServiceId),
            $entityClass,
            $authAttr->emailField,
            $authAttr->passwordField,
            $authAttr->rolesField,
        ]);
        $definition->setShared(true);
        $definition->setPublic(false);
        $definition->setAutowired(false);

        $container->setDefinition(ReflectiveUserProvider::class, $definition);
        $container->setAlias(UserProviderInterface::class, ReflectiveUserProvider::class)
            ->setPublic(false);
    }

    /**
     * Find the repository service ID for the given entity class.
     *
     * Looks for a service that:
     *   - Has a findById() method whose return type matches $entityClass
     *   - OR is named after the entity (e.g. UserRepository for User entity)
     */
    private function findRepository(ContainerBuilder $container, string $entityClass): ?string
    {
        $shortName = (new ReflectionClass($entityClass))->getShortName();
        $repositoryPatterns = [
            $shortName . 'Repository',
            'App\\' . $shortName . '\\Infrastructure\\Repository\\' . $shortName . 'Repository',
        ];

        foreach ($container->getDefinitions() as $serviceId => $definition) {
            $class = $definition->getClass() ?? $serviceId;

            // Match by class name pattern
            foreach ($repositoryPatterns as $pattern) {
                if (str_ends_with($class, $pattern) || str_ends_with($serviceId, $pattern)) {
                    return $serviceId;
                }
            }
        }

        // Fallback: look for WriteRepositoryInterface implementations
        // that have findByEmail method (required for auth)
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            $class = $definition->getClass();

            if ($class === null || !class_exists($class)) {
                continue;
            }

            if (method_exists($class, 'findByEmail') && method_exists($class, 'findById')) {
                return $serviceId;
            }
        }

        return null;
    }
}
