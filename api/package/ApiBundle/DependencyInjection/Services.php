<?php

namespace Package\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    // Set Autoconfigure
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    // Event Listener
    $services->load('Package\\ApiBundle\\EventListener\\', '../EventListener/');

    // Commands
    $services->load('Package\\ApiBundle\\Command\\', '../Command/');

    // Argument Resolver
    $services->load('Package\\ApiBundle\\ArgumentResolver\\', '../ArgumentResolver/');

    // Api-DOC
    if ('prod' !== $container->env()) {
        $services->load('Package\\ApiBundle\\Documentation\\', '../Documentation/');
    }
};
