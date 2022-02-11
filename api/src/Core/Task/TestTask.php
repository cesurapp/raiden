<?php

namespace App\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TestTask implements TaskInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function __invoke(bool|array|string|null $data): void
    {
        usleep(10000);
    }
}