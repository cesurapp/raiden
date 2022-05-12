<?php

namespace Package\SwooleBundle\Test;

use Package\SwooleBundle\Cron\CronWorker;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CronTest extends KernelTestCase
{
    public function testCronHandler(): void
    {
        $container = static::bootKernel()->getContainer();
        $this->assertTrue($container->has(CronWorker::class));

        /** @var CronWorker $worker */
        $worker = $container->get(CronWorker::class);

        try {
            $worker->run();
        } catch (\Exception $exception) {
            $this->throwException($exception);
        }
    }

    public function testCronListCommand(): void
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $cmd = $application->find('cron:list');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
    }
}
