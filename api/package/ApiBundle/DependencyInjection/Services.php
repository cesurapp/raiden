<?php

namespace Package\ApiBundle\DependencyInjection;

use Package\ApiBundle\Thor\Controller\ThorController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    // Set Autoconfigure
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    // Event Listener
    $services->load('Package\\ApiBundle\\EventListener\\', '../EventListener/');

    // Argument Resolver
    $services->load('Package\\ApiBundle\\ArgumentResolver\\', '../ArgumentResolver/');

    // Thor
    $services->load('Package\\ApiBundle\\Thor\\Command\\', '../Thor/Command/');
    $services->set(ThorController::class, ThorController::class);
};
