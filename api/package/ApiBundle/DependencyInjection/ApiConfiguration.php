<?php

namespace Package\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ApiConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('api_bundle');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('apidoc_prod')->defaultFalse()->end()
                ->scalarNode('apidoc_path')->defaultValue('')->end()
                ->scalarNode('apidoc_global_config')->defaultValue('')->end()
                ->scalarNode('apidoc_base_url')->defaultValue('')->end()
            ->end();

        return $treeBuilder;
    }
}
