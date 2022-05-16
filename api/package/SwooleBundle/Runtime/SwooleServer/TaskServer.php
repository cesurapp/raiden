<?php

namespace Package\SwooleBundle\Runtime\SwooleServer;

use Package\SwooleBundle\Task\TaskWorker;
use Swoole\Server\Task;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TaskServer
{
    private TaskWorker $taskWorker;

    public function __construct(private HttpKernelInterface $application, private HttpServer $server, private array $options)
    {
        if ($this->options['app']['task']) {
            // Init Worker
            $kernel = clone $this->application;
            $kernel->boot(); // @phpstan-ignore-line
            $this->taskWorker = $kernel->getContainer()->get(TaskWorker::class); // @phpstan-ignore-line

            // Add Task Event
            $this->server->on('task', [$this, 'onTask']);
        }
    }

    /**
     * Handle Task.
     */
    public function onTask(HttpServer $server, Task $task): void
    {
        $this->taskWorker->handle($task->data);
    }
}
