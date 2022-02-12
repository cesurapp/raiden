<?php

namespace Package\SwooleBundle\Cron;

interface CronInterface
{
    public const TIME = '';
    public const ENABLED = true;

    public function __invoke(): void;
}
