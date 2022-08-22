<?php

namespace Package\SwooleBundle\Runtime\SwooleServer;

use Package\SwooleBundle\Cron\CronWorker;
use Swoole\Timer;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CronServer
{
    public function __construct(HttpKernelInterface $application, HttpServer $server, array $options)
    {
        if ($options['app']['cron']) {
            $kernel = clone $application;
            $kernel->boot(); // @phpstan-ignore-line
            $worker = $kernel->getContainer()->get(CronWorker::class); // @phpstan-ignore-line

            // Work
            $server->on('start', function () use ($options, $worker) {
                Timer::tick(
                    $options['cron']['interval'],
                    static fn () => $worker->run()
                );
            });
        }
    }
}
