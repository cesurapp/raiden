<?php

namespace Package\SwooleBundle\Test;

use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{
    public function testTaskHandler(): void
    {
        $container = static::bootKernel()->getContainer();
        $this->assertTrue($container->has(TaskWorker::class));

        /** @var TaskWorker $worker */
        $worker = $container->get(TaskWorker::class);
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
}
