<?php

namespace Package\ApiBundle\DependencyInjection;

use Package\ApiBundle\Response\ApiResourceLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Init Task Worker
        $resources = $container->findTaggedServiceIds('resources');
        array_walk($resources, static fn (&$val, $id) => $val = new Reference($id));
        $container
            ->register(ApiResourceLocator::class, ApiResourceLocator::class)
            ->addArgument(ServiceLocatorTagPass::register($container, $resources))
            ->setAutowired(true)
            ->setPublic(true);
    }
}
