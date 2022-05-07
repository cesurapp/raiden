<?php

namespace Package\StorageBundle\DependencyInjection;

use Package\StorageBundle\Driver\BackBlaze;
use Package\StorageBundle\Driver\DOSpaces;
use Package\StorageBundle\Driver\Linode;
use Package\StorageBundle\Driver\Local;
use Package\StorageBundle\Driver\S3;
use Package\StorageBundle\Driver\Wasabi;
use Package\StorageBundle\Storage\Storage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class StorageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Register Devices
        $config = $this->processConfiguration(new StorageConfiguration(), $configs);
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
            $deviceDefinitions[$device] = $container->setDefinition($device, new Definition(
                $class,
                array_values(array_intersect_key($value, array_flip($constructors)))
            ));
        }

        // Register Storage
        $container->setDefinition(Storage::class, new Definition(Storage::class, [
            '$default' => $config['default'],
            '$devices' => $deviceDefinitions,
        ]));
    }
}
