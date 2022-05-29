<?php

namespace Package\MediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class MediaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Load Services
        (new PhpFileLoader($container, new FileLocator(__DIR__)))->load('Services.php');
    }
}
