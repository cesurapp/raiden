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
        $container
            ->register(TaskWorker::class, TaskWorker::class)
            ->addArgument(
                ServiceLocatorTagPass::register(
                    $container,
                    array_map(static fn ($id) => new Reference($id), array_keys($container->findTaggedServiceIds('tasks')))
                )
            )
            ->setPublic(true);

        // Init Cron Worker
        $container
            ->register(CronWorker::class, CronWorker::class)
            ->addArgument(
                ServiceLocatorTagPass::register(
                    $container,
                    array_map(static fn ($id) => new Reference($id), array_keys($container->findTaggedServiceIds('crons')))
                )
            )
            ->setPublic(true);
    }
}
