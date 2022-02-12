<?php

namespace Package\SwooleBundle\Task;

class TaskHandler
{
    public static function dispatch(TaskInterface|string $task, mixed $payload = null): void
    {
        if (empty($server = $GLOBALS['http_server'])) {
            throw new \RuntimeException('HTTP Server not found!');
        }

        $server->task([
            'class' => is_string($task) ? $task : get_class($task),
            'payload' => $payload,
        ]);
    }
}
