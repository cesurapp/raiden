<?php

namespace Package\MediaBundle;

use Doctrine\DBAL\Types\Type;
use Package\MediaBundle\Entity\Media;
use Package\MediaBundle\EventListener\MediaColumnListener;
use Package\MediaBundle\EventListener\MediaRemovedListener;
use Package\MediaBundle\Manager\MediaManager;
use Package\MediaBundle\Type\MediaType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MediaBundle extends AbstractBundle
{
    public function __construct()
    {
        if (!Type::getTypeRegistry()->has('media')) {
            Type::getTypeRegistry()->register('media', new MediaType());
        }
    }

    public function boot(): void
    {
        /** @var MediaType $type */
        $type = Type::getType('media');
        $type->setEntityManager($this->container->get('doctrine')->getManager());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Set Autoconfigure
        $services = $container->services()->defaults()->autowire()->autoconfigure();

        // Commands
        $services->load('Package\\MediaBundle\\Command\\', 'Command');

        // Repository
        $services->load('Package\\MediaBundle\\Repository\\', 'Repository');

        // Media Manager
        $manager = $services->set(MediaManager::class, MediaManager::class);
        if ('test' === $container->env()) {
            $manager->public();
        }

        // Media Event Listener
        $services->set(MediaColumnListener::class)->tag('doctrine.event_subscriber', [
            'priority' => 500,
            'connection' => 'default',
        ]);

        $services->set(MediaRemovedListener::class)->tag('doctrine.orm.entity_listener', [
            'event' => 'postRemove',
            'entity' => Media::class,
        ]);
    }
}
