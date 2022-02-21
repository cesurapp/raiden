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
        $tasks = $container->findTaggedServiceIds('tasks');
        array_walk($tasks, static fn (&$val, $id) => $val = new Reference($id));
        $container
            ->register(TaskWorker::class, TaskWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($container, $tasks)) // @phpstan-ignore-line
            ->setAutowired(true)
            ->setPublic(true);

        // Init Cron Worker
        $crons = $container->findTaggedServiceIds('crons');
        array_walk($crons, static fn (&$val, $id) => $val = new Reference($id));
        $container
            ->register(CronWorker::class, CronWorker::class)
            ->addArgument(ServiceLocatorTagPass::register($container, $crons)) // @phpstan-ignore-line
            ->setAutowired(true)
            ->setPublic(true);
    }
}
