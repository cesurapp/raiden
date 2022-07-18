<?php

namespace Package\StorageBundle;

use Package\StorageBundle\Driver\BackBlaze;
use Package\StorageBundle\Driver\Cloudflare;
use Package\StorageBundle\Driver\Local;
use Package\StorageBundle\Storage\Storage;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StorageBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        // Storage Configuration
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->scalarNode('default')->isRequired()->end()
                    ->arrayNode('devices')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                        ->children()
                            ->enumNode('driver')->isRequired()->values(['local', 'cloudflare'])->end()
                            ->scalarNode('root')->isRequired()->end()
                            ->scalarNode('accessKey')->defaultValue('')->end()
                            ->scalarNode('secretKey')->defaultValue('')->end()
                            ->scalarNode('bucket')->defaultValue('')->end()
                            ->scalarNode('region')->defaultValue('')->end()
                            ->scalarNode('endPoint')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Register Devices
        $deviceDefinitions = [];
        foreach ($config['devices'] as $device => $value) {
            $class = match ($value['driver']) {
                'cloudflare' => Cloudflare::class,
                'backblaze' => BackBlaze::class,
                default => Local::class
            };

            $ref = new \ReflectionClass($class);
            $constructors = array_map(static fn (\ReflectionParameter $param) => $param->name, $ref->getConstructor()->getParameters());

            // Set Service
            $definition = new Definition($class);
            $initData = array_intersect_key($value, array_flip($constructors));
            foreach ($initData as $key => $val) {
                $definition->setArgument("$$key", $val);
            }
            $deviceDefinitions[$device] = $builder->setDefinition($device, $definition);
        }

        // Register Storage
        $builder->setDefinition(Storage::class, new Definition(Storage::class, [
            '$default' => $config['default'],
            '$devices' => $deviceDefinitions,
        ]));
    }
}
