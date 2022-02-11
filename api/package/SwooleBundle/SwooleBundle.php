<?php

namespace Package\SwooleBundle;

use Package\SwooleBundle\DependencyInjection\SwooleCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwooleBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new SwooleCompilerPass());
    }
}
