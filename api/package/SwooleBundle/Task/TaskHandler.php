<?php

namespace Package\SwooleBundle\Task;

use Swoole\Http\Server;
use Symfony\Component\HttpKernel\KernelInterface;

class TaskHandler
{
    private Server $server;

    public function __construct(KernelInterface $kernel)
    {
        $this->server = $kernel->getServer(); // @phpstan-ignore-line
    }

    public function dispatch(TaskInterface|string $task, mixed $payload = null, ?callable $finishCallback = null): void
    {
        $this->server->task([
            'class' => is_string($task) ? $task : get_class($task),
            'payload' => $payload,
        ], -1, $finishCallback);
    }
}
