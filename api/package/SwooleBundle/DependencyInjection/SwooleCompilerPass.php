<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Cron\CronWorker;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SwooleCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Init Task Worker
        $tasks = [];
        foreach ($container->findTaggedServiceIds('tasks') as $serviceId => $value) {
            $tasks[$serviceId] = new Reference($serviceId);
        }
        $container
            ->register(TaskWorker::class, TaskWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($container, $tasks))
            ->setPublic(true);

        // Init Cron Worker
        $crons = [];
        foreach ($container->findTaggedServiceIds('crons') as $serviceId => $value) {
            $crons[$serviceId] = new Reference($serviceId);
        }
        $container
            ->register(CronWorker::class, CronWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($container, $crons))
            ->setPublic(true);
    }
}
