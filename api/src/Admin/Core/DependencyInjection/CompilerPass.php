<?php

namespace App\Admin\Core\DependencyInjection;

use App\Admin\Core\Service\PermissionManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->register(PermissionManager::class, PermissionManager::class)
            ->addArgument(PermissionManager::findPermission($container));
    }
}
