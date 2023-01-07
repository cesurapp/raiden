<?php

namespace Package\SwooleBundle\Runtime\SwooleServer;

use Swoole\Constant;
use Swoole\Process;

class TcpServer
{
    public function __construct(HttpServer $server, private readonly array $options)
    {
        if ($this->options['app']['tcp']) {
            $tcpServer = $server->addlistener(
                $this->options['tcp']['host'],
                $this->options['tcp']['port'],
                $this->options['tcp']['sock_type']
            );
            $tcpServer->set([Constant::OPTION_WORKER_NUM => 1]);
            $tcpServer->on('receive', [$this, 'onReceive']);
        }
    }

    /**
     * Handle TCP Request.
     */
    public function onReceive(HttpServer $server, int $fd, int $fromId, string $command): void
    {
        $cmd = explode('::', $command);

        $result = match ($cmd[0]) {
            'shutdown' => $this->cmdShutdown($server),
            'taskRetry' => $this->cmdTaskRetry($server, $cmd[1]),
            'getMetrics' => $this->cmdMetrics($server),
            default => 0
        };

        $server->send($fd, $result);
    }

    private function cmdShutdown(HttpServer $server): int
    {
        Process::kill($server->master_pid);

        return 1;
    }

    private function cmdTaskRetry(HttpServer $server, string $cmd): int
    {
        $server->task(json_decode($cmd, true, 512, JSON_THROW_ON_ERROR));

        return 1;
    }

    private function cmdMetrics(HttpServer $server): string
    {
        $options = $this->options;
        $options['cache_table']['current'] = $server->appCache->count();
        $options['cache_table']['memory_size'] = $server->appCache->memorySize;

        return json_encode([
            'server' => $options,
            'metrics' => $server->stats(),
        ], JSON_THROW_ON_ERROR);
    }
}
