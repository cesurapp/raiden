<?php

namespace App\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;

class TestTask implements TaskInterface
{
    public function __invoke(mixed $data = null): void
    {
        dump('nalet');
    }
}
