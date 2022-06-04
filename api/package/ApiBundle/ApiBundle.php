<?php

namespace Package\ApiBundle;

use Package\ApiBundle\DependencyInjection\ApiCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ApiCompilerPass());

        parent::build($container);
    }
}
