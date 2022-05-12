<?php

namespace Package\SwooleBundle\Task;

class TaskHandler
{
    public static function dispatch(TaskInterface|string $task, mixed $payload = null): void
    {
        if ('test' === $_SERVER['APP_ENV']) {
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
