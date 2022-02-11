<?php

namespace App\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;

class TestTask implements TaskInterface
{
    public function __invoke(bool|array|string|null $data): void
    {
        dump('djkshdakdashdkasjdhsakdasdhsakdjaskdhaskjhdsakjdashdjashdksdhskaj');
    }
}