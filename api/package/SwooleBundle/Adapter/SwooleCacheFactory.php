<?php

namespace Package\SwooleBundle\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Console\Application;

class SwooleCacheFactory
{
    public static function createAdapter(string $namespace = '', int $defaultLifetime = 0): AdapterInterface
    {
        return $GLOBALS['app'] instanceof Application ? new ArrayAdapter() : new SwooleCacheAdapter($namespace, $defaultLifetime);
    }
}
