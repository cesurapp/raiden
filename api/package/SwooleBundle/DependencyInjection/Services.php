<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Adapter\SwooleCacheAdapter;
use Package\SwooleBundle\Adapter\SwooleCacheFactory;
use Package\SwooleBundle\Log\Logger;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    // Set Autoconfigure
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    // Swoole Cache Adapter
    $container->services()
        ->set('cache.adapter.swoole', SwooleCacheAdapter::class)
        ->abstract()
        ->factory([SwooleCacheFactory::class, 'createAdapter'])
        ->args(['', 0])
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('cache.pool', ['clearer' => 'cache.default_clearer', 'reset' => 'reset'])
        ->tag('monolog.logger', ['channel' => 'cache']);

    // Commands
    $services
        ->load('Package\\SwooleBundle\\Command\\', '../Command/');

    // Logger
    $container->services()
        ->set('logger', Logger::class)
        ->args([
            '$formatter' => null,
            '$minLevel' => '%env(LOG_LEVEL)%',
            '$output' => '%kernel.logs_dir%/%env(APP_ENV)%.log',
            '$stdin' => '%env(LOG_STDIN)%',
        ]);
};