<?php

namespace Package\SwooleBundle\Task;

class TaskHandler
{
    public static function dispatch(TaskInterface|string $task, mixed $payload = null): void
    {
        if (!isset($GLOBALS['http_server'])) {
            throw new \RuntimeException('HTTP Server not found!');
        }

        $GLOBALS['http_server']->task([
            'class' => is_string($task) ? $task : get_class($task),
            'payload' => $payload,
        ]);
    }
}
