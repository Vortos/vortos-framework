<?php
declare(strict_types=1);
namespace Vortos\Cqrs\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Vortos\Cqrs\Command\Idempotency\RedisCommandIdempotencyStore;

/**
 * Validates the vortos_cqrs configuration tree.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('vortos_cqrs');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('command_bus')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('idempotency_store')
                            ->defaultValue(RedisCommandIdempotencyStore::class)
                            ->info('FQCN of CommandIdempotencyStoreInterface implementation')
                        ->end()
                        ->integerNode('idempotency_ttl')
                            ->defaultValue(86400)
                            ->info('Seconds before idempotency key expires')
                        ->end()
                        ->booleanNode('strict_idempotency')
                            ->defaultFalse()
                            ->info('Throw DuplicateCommandException instead of silently skipping')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}