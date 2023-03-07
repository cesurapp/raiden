<?php

namespace Package\SwooleBundle\Test;

use Package\SwooleBundle\Runtime\SwooleProcess;
use OpenSwoole\Constant;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServerTest extends KernelTestCase
{
    public function testHttpServer(): void
    {
        $kernel = static::createKernel();
        $output = new SymfonyStyle(new ArgvInput(), new NullOutput());
        $process = new SwooleProcess($output, $kernel->getProjectDir());

        // Start Server
        $server = $process->start(PHP_BINARY, [
            'http' => [
                'host' => '0.0.0.0',
                'port' => 9599,
                'settings' => [
                    'worker_num' => 1,
                    'task_worker_num' => 1,
                    'pid_file' => sprintf('%s/var/test_server.pid', $kernel->getProjectDir()),
                    'log_file' => sprintf('%s/var/log/%s_server.log', $kernel->getProjectDir(), $kernel->getEnvironment()),
                ],
            ],
            'tcp' => [
                'host' => '127.0.0.1',
                'port' => 9550,
                'sock_type' => Constant::SOCK_TCP,
            ],
            'app' => [
                'watch' => 0,
                'cron' => 0,
                'task' => 1,
                'tcp' => 1,
            ],
        ]);

        sleep(1);
        $this->assertTrue($server);
        $this->assertFileExists(sprintf('%s/var/test_server.pid', $kernel->getProjectDir()));
        $this->assertFileExists(
            sprintf('%s/var/log/%s_server.log', $kernel->getProjectDir(), $kernel->getEnvironment())
        );
        $this->assertTrue($process->stop('127.0.0.1', 9550));
    }
}
