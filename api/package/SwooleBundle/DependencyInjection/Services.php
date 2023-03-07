<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Adapter\SwooleCacheAdapter;
use Package\SwooleBundle\Adapter\SwooleCacheFactory;
use Package\SwooleBundle\Task\FailedTaskCron;
use Package\SwooleBundle\Task\TaskHandler;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger;

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
    $services->load('Package\\SwooleBundle\\Command\\', '../Command/');

    // Repository
    $services->load('Package\\SwooleBundle\\Repository\\', '../Repository');

    // Logger
    $container->services()
        ->set('logger', Logger::class)
        ->args(['%env(APP_LOG_LEVEL)%', '%kernel.logs_dir%/%env(APP_ENV)%.log', null, new Reference(RequestStack::class)]);

    // Task Handler
    $taskHandler = $services->set(TaskHandler::class, TaskHandler::class);
    if ('test' === $container->env()) {
        $taskHandler->args([
            '$worker' => $services->get(TaskWorker::class),
        ]);
    }

    // Failed Task Cron
    $services->set(FailedTaskCron::class, FailedTaskCron::class);
};
