<?php

namespace Package\SwooleBundle\Task;

use Swoole\Http\Server;
use Symfony\Component\HttpFoundation\RequestStack;

class TaskHandler
{
    private Server $server;

    public function __construct(RequestStack $requestStack)
    {
        $this->server = $requestStack->getMainRequest()->attributes->get('_server');
    }

    public function dispatch(TaskInterface $task, string|array|bool|null $data = null, ?callable $finishCallback = null): void
    {
        $this->server->task(['class' => get_class($task), 'data' => $data], -1, $finishCallback);
    }
}