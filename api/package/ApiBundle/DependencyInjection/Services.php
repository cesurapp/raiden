<?php

namespace Package\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Package\ApiBundle\Documentation\ApiDocController;

return static function (ContainerConfigurator $container) {
    // Set Autoconfigure
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    // Event Listener
    $services->load('Package\\ApiBundle\\EventListener\\', '../EventListener/');

    // Commands
    $services->load('Package\\ApiBundle\\Command\\', '../Command/');

    // Argument Resolver
    $services->load('Package\\ApiBundle\\ArgumentResolver\\', '../ArgumentResolver/');

    // ApiDoc
    $services->set(ApiDocController::class, ApiDocController::class);
};
