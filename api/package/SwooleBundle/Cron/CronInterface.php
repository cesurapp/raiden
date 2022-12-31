<?php

namespace Package\SwooleBundle\Cron;

interface CronInterface
{
    public function __invoke(): void;
}
