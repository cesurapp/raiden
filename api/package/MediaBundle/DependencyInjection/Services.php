<?php

namespace Package\MediaBundle\DependencyInjection;

use Package\MediaBundle\EventListener\MediaListener;
use Package\MediaBundle\Manager\MediaManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    // Set Autoconfigure
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    // Commands
    $services->load('Package\\MediaBundle\\Command\\', '../Command/');

    // Repository
    $services->load('Package\\MediaBundle\\Repository\\', '../Repository');

    // Media Manager
    $services->set(MediaManager::class, MediaManager::class);

    // Media Event Listener
    $services->set(MediaListener::class)->tag('doctrine.event_subscriber', [
        'priority' => 500,
        'connection' => 'default',
    ]);
};
