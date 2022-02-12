<?php

namespace App\Core\Task;

use Package\SwooleBundle\Cron\CronInterface;

class TestCron implements CronInterface
{
    public function __invoke(): void
    {
        dump('testcron');
    }
}