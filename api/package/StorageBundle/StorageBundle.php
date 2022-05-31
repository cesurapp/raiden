<?php

namespace Package\StorageBundle;

use Package\StorageBundle\Driver\BackBlaze;
use Package\StorageBundle\Driver\DOSpaces;
use Package\StorageBundle\Driver\Linode;
use Package\StorageBundle\Driver\Local;
use Package\StorageBundle\Driver\S3;
use Package\StorageBundle\Driver\Wasabi;
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
        // Thor Configuration
        $definition->rootNode() // @phpstan-ignore-line
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
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Register Devices
        $deviceDefinitions = [];
        foreach ($config['devices'] as $device => $value) {
            $class = match ($value['driver']) {
                's3' => S3::class,
                'linode' => Linode::class,
                'dospaces' => DOSpaces::class,
                'backblaze' => BackBlaze::class,
                'wasabi' => Wasabi::class,
                default => Local::class
            };

            $ref = new \ReflectionClass($class);
            $constructors = array_map(static fn (\ReflectionParameter $param) => $param->name, $ref->getConstructor()->getParameters());

            // Set Service
            $deviceDefinitions[$device] = $builder->setDefinition($device, new Definition(
                $class,
                array_values(array_intersect_key($value, array_flip($constructors)))
            ));
        }

        // Register Storage
        $builder->setDefinition(Storage::class, new Definition(Storage::class, [
            '$default' => $config['default'],
            '$devices' => $deviceDefinitions,
        ]));
    }
}
