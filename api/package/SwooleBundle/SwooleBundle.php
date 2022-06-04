<?php

namespace Package\SwooleBundle;

use Package\SwooleBundle\Adapter\SwooleCacheAdapter;
use Package\SwooleBundle\Adapter\SwooleCacheFactory;
use Package\SwooleBundle\Cron\CronInterface;
use Package\SwooleBundle\Cron\CronWorker;
use Package\SwooleBundle\Log\Logger;
use Package\SwooleBundle\Task\TaskInterface;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class SwooleBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Register Task & Cron Services
        $builder->registerForAutoconfiguration(TaskInterface::class)
            ->addTag('tasks')
            ->setLazy(true);
        $builder->registerForAutoconfiguration(CronInterface::class)
            ->addTag('crons')
            ->setLazy(true);

        // Init Task Worker
        $tasks = $builder->findTaggedServiceIds('tasks');
        array_walk($tasks, static fn (&$val, $id) => $val = new Reference($id));
        $builder
            ->register(TaskWorker::class, TaskWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($builder, $tasks))
            ->setAutowired(true)
            ->setPublic(true);

        // Init Cron Worker
        $crons = $builder->findTaggedServiceIds('crons');
        array_walk($crons, static fn (&$val, $id) => $val = new Reference($id));
        $builder
            ->register(CronWorker::class, CronWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($builder, $crons))
            ->setAutowired(true)
            ->setPublic(true);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Load Services
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
        $services->load('Package\\SwooleBundle\\Command\\', 'Command');

        // Repository
        $services->load('Package\\SwooleBundle\\Repository\\', 'Repository');

        // Logger
        $logger = $container->services()
            ->set('logger', Logger::class)
            ->args([
                '$formatter' => null,
                '$minLevel' => '%env(APP_LOG_LEVEL)%',
                '$output' => '%kernel.logs_dir%/%env(APP_ENV)%.log',
                '$stdin' => '%env(APP_LOG_STDIN)%',
            ]);
        if ('test' === $container->env()) {
            $logger->public();
        }
    }
}
