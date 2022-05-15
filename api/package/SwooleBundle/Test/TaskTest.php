<?php

namespace Package\SwooleBundle\Test;

use Doctrine\ORM\Tools\SchemaTool;
use Package\SwooleBundle\Entity\FailedTask;
use Package\SwooleBundle\Log\Logger;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class TaskTest extends WebTestCase
{
    protected function setUp(): void
    {
    }

    public function testTaskHandler(): void
    {
        $client = self::createClient();
        $this->assertTrue($client->getContainer()->has(TaskWorker::class));
    }

    public function testFailedCreate(): void
    {
        $client = self::createClient();

        // Mock Logger
        $logger = $this->getMockBuilder(Logger::class)->getMock();
        $container = $client->getContainer();
        $container->set('logger', $logger);

        // Test Worker
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($client->getKernel());

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $logger->expects($this->once())->method('critical');
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $this->assertEquals(1, $container->get('doctrine')->getRepository(FailedTask::class)->count([]));
    }

    public function testFailedClear(): void
    {
        $client = self::createClient();

        // Mock Logger
        $logger = $this->getMockBuilder(Logger::class)->getMock();
        $container = $client->getContainer();
        $container->set('logger', $logger);

        // Test Worker
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($client->getKernel());

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $logger->expects($this->once())->method('critical');
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $application = new Application(static::$kernel);

        $cmd = $application->find('task:failed:clear');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
    }

    public function testFailedView(): void
    {
        $client = self::createClient();

        // Mock Logger
        $logger = $this->getMockBuilder(Logger::class)->getMock();
        $container = $client->getContainer();
        $container->set('logger', $logger);

        // Test Worker
        $this->assertTrue($container->has(TaskWorker::class));

        // Init DB
        $this->initDatabase($client->getKernel());

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
        $logger->expects($this->once())->method('critical');
        $worker->handle([
            'class' => 'TestTaskClass',
            'data' => [],
        ]);

        $application = new Application(static::$kernel);

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
