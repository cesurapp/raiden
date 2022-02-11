<?php

namespace Package\SwooleBundle\Cron;

use Symfony\Component\DependencyInjection\ServiceLocator;

class CronWorker
{
    public function __construct(private ServiceLocator $locator)
    {
    }

    public function run(): void
    {
        dump('running');
    }

    public function failedCron(CronInterface $cron, \Exception $exception): void
    {
    }
}
