<?php

namespace Package\SwooleBundle\Test;

use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Package\SwooleBundle\Entity\FailedTask;
use Package\SwooleBundle\Log\Logger;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

class TaskTest extends KernelTestCase
{
    public function testTaskHandler(): void
    {
        $client = self::bootKernel();
        $this->assertTrue($client->getContainer()->has(TaskWorker::class));
    }

    public function testFailedCreate(): void
    {
        $kernel = new CustomKernel('test', true);
        $kernel->boot();

        // Test Worker
        $container = $kernel->getContainer();
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($kernel);

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $this->assertGreaterThanOrEqual(1, $container->get('doctrine')->getRepository(FailedTask::class)->count([]));
    }

    public function testFailedClear(): void
    {
        $kernel = new CustomKernel('test', true);
        $kernel->boot();

        // Test Worker
        $container = $kernel->getContainer();
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($kernel);

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $application = new Application($kernel);

        $cmd = $application->find('task:failed:clear');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
    }

    public function testFailedView(): void
    {
        $kernel = new CustomKernel('test', true);
        $kernel->boot();

        // Test Worker
        $container = $kernel->getContainer();
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($kernel);

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $application = new Application($kernel);

        $cmd = $application->find('task:failed:view');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('TestTaskClass', $cmdTester->getDisplay());
    }

    private function initDatabase(KernelInterface $kernel): void
    {
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }
}

class CustomKernel extends Kernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->register('logger', Logger::class)
            ->setArguments([
                '$formatter' => null,
                '$minLevel' => 'debug',
                '$output' => '%kernel.logs_dir%/%env(APP_ENV)%.log',
                '$stdin' => 0,
            ]);
    }
}
