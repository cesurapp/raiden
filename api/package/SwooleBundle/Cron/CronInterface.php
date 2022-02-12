<?php

namespace Package\SwooleBundle\Cron;

interface CronInterface
{
    public const time = '';

    public function __invoke(): void;
}
