<?php

namespace App\Core\Task;

use Package\SwooleBundle\Cron\CronInterface;

class TestCron implements CronInterface
{
    public const TIME = '@EveryMinute10';
    public const ENABLE = true;

    public function __invoke(): void
    {
        dump('testcron');
    }
}