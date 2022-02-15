<?php

namespace App\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Package\SwooleBundle\Task\TaskNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;

class TestTask implements TaskInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function __invoke(mixed $data = null): void
    {
        dump('testtask reload');
     //   throw new TaskNotFoundException();
        // TODO: Implement __invoke() method.
    }

}