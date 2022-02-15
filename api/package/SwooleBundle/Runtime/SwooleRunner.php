<?php

namespace Package\SwooleBundle\Runtime;

use App\Kernel;
use Package\SwooleBundle\Cron\CronWorker;
use Package\SwooleBundle\Task\TaskWorker;
use Swoole\Constant;
use Swoole\Coroutine\System;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Server as TcpServer;
use Swoole\Table;
use Swoole\Timer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Runtime\RunnerInterface;

class SwooleRunner implements RunnerInterface
{
    /**
     * Swoole HTTP Server.
     */
    private Server $server;

    /**
     * Swoole Task Worker.
     */
    private TaskWorker $taskWorker;

    /**
     * Swoole Config.
     */
    public static array $options = [
        'http' => [
            'host' => '0.0.0.0',
            'port' => 80,
            'mode' => SWOOLE_PROCESS,
            'sock_type' => SWOOLE_SOCK_TCP,
            'settings' => [
                Constant::OPTION_WORKER_NUM => 8,
                Constant::OPTION_TASK_WORKER_NUM => 4,
                Constant::OPTION_ENABLE_STATIC_HANDLER => false,
                Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_ERROR,
            ],
        ],
        'tcp' => [
            'host' => '127.0.0.1',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
        ],
        'cache_table' => [
            'size' => 500,
            'size_value' => 500,
            'current' => 0,
        ],
        'cron' => [
            'failed' => 0,
            'completed' => 0,
            'interval' => 1000 * 60,
        ],
        'task' => [
            'failed' => 0,
            'completed' => 0,
        ],
        'app' => [
            'env' => 'prod',
            'watch' => 0,
            'cron' => 1,
            'task' => 1,
        ],
    ];

    public function __construct(private HttpKernelInterface $application, array $options)
    {
        self::$options['app']['env'] = $_ENV['APP_ENV'];
        self::$options = array_merge(self::$options, $options);
        if (isset($_SERVER['watch'])) {
            self::$options['app']['watch'] = $_SERVER['watch'];
        }
    }

    public function run(): int
    {
        // Create Server
        $this->createHttpServer();
        $this->createTCPServer();
        $this->initCacheTable();

        // Init Task & Cron
        $this->initCron();
        $this->initTask();

        // Start Server
        return (int) $this->server->start();
    }

    /**
     * Create HTTP Server.
     */
    private function createHttpServer(): void
    {
        // Create HTTP Server
        $this->server = new Server(
            self::$options['http']['host'],
            (int) self::$options['http']['port'],
            (int) (self::$options['http']['mode']),
            (int) (self::$options['http']['sock_type'])
        );
        $this->server->set(self::$options['http']['settings']);
        $GLOBALS['http_server'] = $this->server;

        // Handle Event
        $this->server->on('request', [$this, 'onRequest']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('start', [$this, 'onStart']);
    }

    /**
     * Create TCP Server.
     */
    private function createTCPServer(): void
    {
        /** @var TcpServer $server */
        $server = $this->server->addlistener(
            self::$options['tcp']['port'],
            self::$options['tcp']['port'],
            self::$options['http']['sock_type']
        );
        $server->set([Constant::OPTION_WORKER_NUM => 1]);
        $server->on('receive', [$this, 'onTcpReceive']);
    }

    /**
     * Create Swoole Cache Table.
     */
    private function initCacheTable(): void
    {
        $table = new Table(self::$options['cache_table']['size']);
        $table->column('value', Table::TYPE_STRING, self::$options['cache_table']['size_value']);
        $table->column('expr', Table::TYPE_INT);
        $table->create();
        $this->server->table = $table; // @phpstan-ignore-line
    }

    /**
     * Start Cron Worker.
     */
    private function initCron(): void
    {
        if (self::$options['app']['cron']) {
            /** @var Kernel $kernel */
            $kernel = clone $this->application;
            $kernel->boot();
            $cronWorker = $kernel->getContainer()->get(CronWorker::class);
            Timer::tick(self::$options['cron']['interval'], static fn () => $cronWorker->run());
        }
    }

    /**
     * Start Task Worker.
     */
    private function initTask(): void
    {
        if (self::$options['app']['task']) {
            /** @var Kernel $kernel */
            $kernel = clone $this->application;
            $kernel->boot();
            $this->taskWorker = $kernel->getContainer()->get(TaskWorker::class);
        }
    }

    /**
     * Handle Request.
     */
    public function onRequest(Request $request, Response $response): void
    {
        if ('/metrics' === $request->server['request_uri']) {
            $this->handleMetrics($request, $response);

            return;
        }

        $sfRequest = SymfonyHttpBridge::convertSwooleRequest($request);
        $sfResponse = $this->application->handle($sfRequest);
        SymfonyHttpBridge::reflectSymfonyResponse($sfResponse, $response);

        if ($this->application instanceof TerminableInterface) {
            $this->application->terminate($sfRequest, $sfResponse);
        }
    }

    /**
     * Handle Server Start Event.
     */
    public function onStart(Server $server): void
    {
        // Shutdown Timer Clearer
        go(static function () {
            if (System::waitSignal(1)) {
                Timer::clearAll();
            }
        });

        // Information
        if (self::$options['app']['watch'] < 2) {
            $output = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
            $output->definitionList(
                'Swoole HTTP Server Information',
                ['Host' => self::$options['http']['host'].':'.self::$options['http']['port']],
                ['TCP Host' => self::$options['tcp']['host'].':'.self::$options['tcp']['port']],
                ['Worker' => self::$options['http']['settings']['worker_num']],
                ['Task Worker' => self::$options['http']['settings']['task_worker_num']],
                ['Debug' => self::$options['debug'] ? 'True' : 'False'],
                [
                    'Log Level' => match (self::$options['http']['settings']['log_level']) {
                        0 => 'SWOOLE_LOG_DEBUG',
                        1 => 'SWOOLE_LOG_TRACE',
                        2 => 'SWOOLE_LOG_INFO',
                        3 => 'SWOOLE_LOG_NOTICE',
                        4 => 'SWOOLE_LOG_WARNING',
                        5 => 'SWOOLE_LOG_ERROR',
                        6 => 'SWOOLE_LOG_NONE',
                        default => '-'
                    },
                ],
                ['Log File' => self::$options['http']['settings']['log_file'] ?? 'STDOUT'],
                ['Environment' => self::$options['app']['env']],
                ['Cron Worker' => self::$options['app']['cron'] ? 'True' : 'False'],
                ['Task Worker' => self::$options['app']['task'] ? 'True' : 'False']
            );
        }
    }

    /**
     * Handle Task.
     */
    public function onTask(Server $server, int $fd, int $reactorId, mixed $data): void
    {
        if (self::$options['app']['task']) {
            $this->taskWorker->handle($data);
        }
    }

    /**
     * TCP Commander.
     */
    public function onTcpReceive(Server $server, int $fd, int $fromId, string $cmd): void
    {
        /* @phpstan-ignore-next-line */
        self::$options['cache_table']['current'] = $server->table->count();

        $result = match ($cmd) {
            'metrics' => json_encode(
                array_merge(['server' => self::$options], [
                    'metrics' => $server->stats(OPENSWOOLE_STATS_DEFAULT),
                ]),
                JSON_THROW_ON_ERROR
            ),
            'shutdown' => Process::kill($this->server->getMasterPid(), 1) && $this->server->shutdown(),
            default => $this->onTcpCommander($cmd)
        };

        $server->send($fd, $result);
    }

    private function onTcpCommander(string $command): int
    {
        $cmd = explode('::', $command);
        if ('task-retry' === $cmd[0]) {
            $this->taskWorker->handle(json_decode($cmd[1], true, 512, JSON_THROW_ON_ERROR));
        }

        return 0;
    }

    /**
     * Handle Swoole Server Metrics.
     */
    private function handleMetrics(Request $req, Response $res): void
    {
        if (isset($req->header['authorization'])) {
            $pass = 'Basic '.base64_encode($_ENV['SERVER_USER'].':'.$_ENV['SERVER_PASS']);
            if ($pass === $req->header['authorization']) {
                $res->header('Content-Type', 'text/plain');
                $res->end($this->server->stats(\OPENSWOOLE_STATS_OPENMETRICS));

                return;
            }
        }

        $res->header('WWW-Authenticate', 'Basic realm="Access denied!"');
        $res->setStatusCode(401);
        $res->end('Access denied!');
    }
}
