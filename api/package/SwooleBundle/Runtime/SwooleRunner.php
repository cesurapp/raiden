<?php

namespace Package\SwooleBundle\Runtime;

use App\Kernel;
use Package\ApiBundle\Utils\Util;
use Swoole\Constant;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Server as TcpServer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;

class SwooleRunner implements RunnerInterface
{
    private Server $server;
    private ContainerInterface $locator;

    public function __construct(private HttpKernelInterface $application, private array $options)
    {
    }

    public function run(): int
    {
        // Create Container
        $kernel = new Kernel($this->options['env'], $this->options['debug']);
        $kernel->boot();
        $this->locator = $kernel->getContainer();

        // Create HTTP Server
        $this->server = new Server(
            $this->options['host'],
            (int)$this->options['port'],
            (int)($this->options['mode'] ?? SWOOLE_PROCESS),
            (int)($this->options['sock_type'] ?? 1)
        );
        $this->server->set($this->options['settings'] ?? []);

        // Handle Event
        $this->server->on('request', [$this, 'onRequest']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('start', [$this, 'onStart']);

        // Create TCP Command Server
        $this->createTCPServer();

        return (int)$this->server->start();
    }


    /**
     * Handle Request
     */
    public function onRequest(Request $req, Response $res): void
    {
        if ($req->server['request_uri'] === '/metrics') {
            $this->handleMetrics($req, $res);
            return;
        }

        // Create HttpFoundation Request
        $sfReq = new SymfonyRequest($req->get ?? [], $req->post ?? [], [], $req->cookie ?? [], $req->files ?? [], array_change_key_case($req->server ?? [], CASE_UPPER), $req->rawContent());
        $sfReq->headers = new HeaderBag($req->header);
        $sfReq->attributes->set('_server', $this->server);

        // Merge Response
        $sfRes = $this->application->handle($sfReq);
        foreach ($sfRes->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $res->header($name, $value);
            }
        }

        $res->status($sfRes->getStatusCode());
        $res->end($sfRes->getContent());
    }

    /**
     * Handle Server Start Event
     */
    public function onStart(Server $server): void
    {
        if ($this->options['watch'] < 2) {
            $io = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
            $io->definitionList('Swoole HTTP Server Information',
                ['Host' => $server->host . ':' . $server->port],
                ['Worker' => $this->options['settings']['worker_num']],
                ['Task Worker' => $this->options['settings']['task_worker_num']],
                ['Debug' => $this->options['debug'] ?? false],
                ['Log Level' => $this->options['settings']['log_level']],
                ['Log File' => str_replace(Util::rootDir(), '', $this->options['settings']['log_file'])],
            );
        }
    }

    /**
     * Handle Task
     */
    public function onTask(Server $server, $taskId, $reactorId, $data): void
    {
        sleep(60);
        //$class = $this->locator->get($data);
    }

    /**
     * TCP Commander Server
     *
     * @return void
     */
    public function createTCPServer(): void
    {
        /** @var TcpServer $server */
        $server = $this->server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP);
        $server->set([
            Constant::OPTION_WORKER_NUM => 1,
            Constant::OPTION_TASK_WORKER_NUM => 0,
        ]);
        $server->on('receive', function (Server $server, $fd, $from_id, $cmd) {
            /** @var TcpServer\Port $port */
            $server->send($fd, $this->tcpCommander($cmd, $this->server));
        });
    }

    public function tcpCommander($cmd, Server $server): string|int
    {
        return match ($cmd) {
            'OPENSWOOLE_STATS_OPENMETRICS' => $this->server->stats(OPENSWOOLE_STATS_OPENMETRICS),
            'OPENSWOOLE_STATS_JSON' => $this->server->stats(OPENSWOOLE_STATS_JSON),
            default => 0
        };
    }

    /**
     * Handle Swoole Server Metrics
     *
     * @param Request $req
     * @param Response $res
     */
    public function handleMetrics(Request $req, Response $res): void
    {
        if (isset($req->header['authorization'])) {
            $pass = 'Basic ' . base64_encode($_ENV['SERVER_USER'] . ':' . $_ENV['SERVER_PASS']);
            if ($pass === $req->header['authorization']) {
                $res->header("Content-Type", "text/plain");
                $res->end($this->server->stats(\OPENSWOOLE_STATS_OPENMETRICS));
                return;
            }
        }

        $res->header('WWW-Authenticate', 'Basic realm="Access denied!"');
        $res->setStatusCode(401);
        $res->end('Access denied!');
    }
}