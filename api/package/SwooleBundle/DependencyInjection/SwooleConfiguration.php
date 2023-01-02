<?php

namespace Package\SwooleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SwooleConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('swoole');

        // Thor Configuration
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('failed_task_attempt')->defaultValue(1)->end()
            ->end();

        return $treeBuilder;
    }
}
