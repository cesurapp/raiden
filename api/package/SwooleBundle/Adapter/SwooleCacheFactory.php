<?php

namespace Package\SwooleBundle\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class SwooleCacheFactory
{
    public static function createAdapter(string $namespace = '', int $defaultLifetime = 0): AdapterInterface
    {
        return isset($GLOBALS['httpServer']) ? new SwooleCacheAdapter($namespace, $defaultLifetime) : new ArrayAdapter();
    }
}
