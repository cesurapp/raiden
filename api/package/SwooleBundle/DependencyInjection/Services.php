<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Adapter\SwooleCacheAdapter2;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('cache.adapter.swoole', SwooleCacheAdapter2::class)
        ->abstract()
        ->args([
            '', // namespace
            0, // default lifetime
        ])
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('cache.pool', ['clearer' => 'cache.default_clearer', 'reset' => 'reset'])
        ->tag('monolog.logger', ['channel' => 'cache'])

        /*
        ->set('cache.adapter.redis', RedisAdapter::class)
        ->abstract()
        ->args([
            abstract_arg('Redis connection service'),
            '', // namespace
            0, // default lifetime
            service('cache.default_marshaller')->ignoreOnInvalid(),
        ])
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('cache.pool', [
            'provider' => 'cache.default_redis_provider',
            'clearer' => 'cache.default_clearer',
            'reset' => 'reset',
        ])
        ->tag('monolog.logger', ['channel' => 'cache'])*/

    ;
};
