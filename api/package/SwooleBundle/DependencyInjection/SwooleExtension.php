<?php

namespace Package\SwooleBundle\DependencyInjection;

use Package\SwooleBundle\Cron\CronInterface;
use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SwooleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Register Configuration
        foreach ($this->processConfiguration(new SwooleConfiguration(), $configs) as $key => $value) {
            $container->getParameterBag()->set('swoole.'.$key, $value);
        }

        // Register Task & Cron Services
        $container->registerForAutoconfiguration(TaskInterface::class)
            ->addTag('tasks')
            ->setLazy(true);
        $container->registerForAutoconfiguration(CronInterface::class)
            ->addTag('crons')
            ->setLazy(true);

        // Load Services
        (new PhpFileLoader($container, new FileLocator(__DIR__)))->load('Services.php');
    }
}
