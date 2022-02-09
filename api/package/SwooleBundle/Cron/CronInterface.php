<?php

namespace Package\SwooleBundle\Cron;

interface CronInterface
{
    public function __invoke(string|array|bool|null $data): void;
}