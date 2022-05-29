<?php

namespace Package\MediaBundle\DependencyInjection;

use Package\MediaBundle\Entity\Media;
use Package\MediaBundle\EventListener\MediaColumnListener;
use Package\MediaBundle\EventListener\MediaRemovedListener;
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
    $services->set(MediaColumnListener::class)->tag('doctrine.event_subscriber', [
        'priority' => 500,
        'connection' => 'default',
    ]);

    $services->set(MediaRemovedListener::class)->tag('doctrine.orm.entity_listener', [
        'event' => 'postRemove',
        'entity' => Media::class,
    ]);
};
