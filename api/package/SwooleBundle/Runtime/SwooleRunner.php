<?php

namespace Package\SwooleBundle\Runtime;

use App\Kernel;
use Package\SwooleBundle\Adapter\SwooleCacheAdapter;
use Swoole\Constant;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Server as TcpServer;
use Swoole\Table;
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
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
        ],
        'cache_table' => [
            'size' => 500,
            'size_value' => 500,
        ],
        'app' => [
            'env' => 'dev',
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
        $this->initTask();
        $this->initCron();

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
        $server->set([
            Constant::OPTION_WORKER_NUM => 1,
            Constant::OPTION_TASK_WORKER_NUM => 0,
        ]);
        $server->on('receive', [$this, 'onTcpReceive']);
    }

    private function initCacheTable(): void
    {
        $table = new Table(self::$options['cache_table']['size']);
        $table->column('value', Table::TYPE_STRING, self::$options['cache_table']['size_value']);
        $table->column('expr', Table::TYPE_INT);
        $table->create();
        $this->server->table = $table;
    }

    private function initCron(): void
    {
    }

    private function initTask(): void
    {
        // Create Container
        $kernel = new Kernel(self::$options['app']['env'], self::$options['debug']);
        $kernel->boot();
        //$this->locator = $kernel->getContainer();
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
        // Info
        if (self::$options['app']['watch'] < 2) {
            $output = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
            $output->definitionList(
                'Swoole HTTP Server Information',
                ['Host' => self::$options['http']['host'].':'.self::$options['http']['port']],
                ['TCP Host' => self::$options['tcp']['host'].':'.self::$options['tcp']['port']],
                ['Worker' => self::$options['http']['settings']['worker_num']],
                ['Task Worker' => self::$options['http']['settings']['task_worker_num']],
                ['Debug' => self::$options['debug'] ? 'True' : 'False'],
                ['Log Level' => match (self::$options['http']['settings']['log_level']) {
                    0 => 'SWOOLE_LOG_DEBUG',
                    1 => 'SWOOLE_LOG_TRACE',
                    2 => 'SWOOLE_LOG_INFO',
                    3 => 'SWOOLE_LOG_NOTICE',
                    4 => 'SWOOLE_LOG_WARNING',
                    5 => 'SWOOLE_LOG_ERROR',
                    6 => 'SWOOLE_LOG_NONE',
                    default => '-'
                }],
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
    public function onTask(Server $server, int $taskId, int $reactorId, mixed $data): void
    {
        //dump($data);
        /*$task = $data['class'];
        $payload = $data['payload'];*/
    }

    /**
     * TCP Commander.
     */
    public function onTcpReceive(Server $server, int $fd, int $fromId, string $cmd): void
    {
        $result = match ($cmd) {
            'metrics' => json_encode(array_merge(self::$options, [
                'metrics' => $server->stats(OPENSWOOLE_STATS_DEFAULT),
            ]), JSON_THROW_ON_ERROR),
            default => 0
        };

        $server->send($fd, $result);
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
