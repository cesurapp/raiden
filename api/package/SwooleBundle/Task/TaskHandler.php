<?php

namespace Package\SwooleBundle\Task;

class TaskHandler
{
    public function __construct(private readonly ?TaskWorker $worker = null)
    {
    }

    public function dispatch(TaskInterface|string $task, mixed $payload = null): void
    {
        if ('test' === $_SERVER['APP_ENV']) {
            $this->worker->handle([
                'class' => is_string($task) ? $task : get_class($task),
                'payload' => $payload,
            ]);

            return;
        }

        if (!isset($GLOBALS['httpServer'])) {
            throw new \RuntimeException('HTTP Server not found!');
        }

        $GLOBALS['httpServer']->task([
            'class' => is_string($task) ? $task : get_class($task),
            'payload' => $payload,
        ]);
    }
}
