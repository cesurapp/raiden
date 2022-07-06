<?php

namespace Package\SwooleBundle;

use Package\SwooleBundle\DependencyInjection\SwooleCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwooleBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SwooleCompilerPass());

        parent::build($container);
    }
}
