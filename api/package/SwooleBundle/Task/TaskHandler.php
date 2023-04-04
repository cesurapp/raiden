<?php

namespace Package\SwooleBundle\Task;

readonly class TaskHandler
{
    public function __construct(private ?TaskWorker $worker = null)
    {
    }

    public function dispatch(TaskInterface|string $task, mixed $payload = null): void
    {
        if ('test' === $_SERVER['APP_ENV']) {
            $this->worker->handle([
                'class' => is_string($task) ? $task : get_class($task),
                'payload' => serialize($payload),
            ]);

            return;
        }

        if (!isset($GLOBALS['httpServer'])) {
            throw new \RuntimeException('HTTP Server not found!');
        }

        $GLOBALS['httpServer']->task([
            'class' => is_string($task) ? $task : get_class($task),
            'payload' => serialize($payload),
        ]);
    }
}
