<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Task\TaskInterface;
use Package\SwooleBundle\Task\TaskLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SwooleCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //$locator = $container->register(TaskLocator::class, TaskLocator::class);
        dump($container->getAlias(TaskInterface::class));
        //$container->findDefinition(TaskInterface::class);
       /* foreach ($container->get(TaskInterface::class) as $taggedServiceId) {
            dump($taggedServiceId);
        }*/
        /*$locator->addArgument(ServiceLocatorTagPass::register($container, [
            TaskInterface::class
        ]));*/

        # Kernel.php
  /*      protected function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(DriverInterface::class)
            ->addTag('app.driver');
    }*/
    }
}
