<?php

namespace Package\SwooleBundle\Test;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ObjectManager;
use Package\SwooleBundle\Log\Logger;
use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TaskTest extends WebTestCase
{
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
    }

    public function testTaskHandler(): void
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
    }

    public function testFailedCreate(): void
    {
    }

    public function testFailedRetry(): void
    {
    }

    public function testFailedClear(): void
    {
    }

    public function testFailedView(): void
    {
    }

    private function initDatabase(KernelInterface $kernel): void
    {
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($metaData);
    }
}
