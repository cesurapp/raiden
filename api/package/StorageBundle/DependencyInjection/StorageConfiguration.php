<?php

namespace Package\StorageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class StorageConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('storage');

        // Thor Configuration
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default')->isRequired()->end()
                ->arrayNode('devices')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('driver')->isRequired()->values(['local', 's3', 'linode', 'dospaces', 'backblaze', 'wasabi'])->end()
                            ->scalarNode('root')->isRequired()->end()
                            ->scalarNode('accessKey')->defaultValue('')->end()
                            ->scalarNode('secretKey')->defaultValue('')->end()
                            ->scalarNode('bucket')->defaultValue('')->end()
                            ->scalarNode('region')->defaultValue('')->end()
                            ->scalarNode('acl')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
