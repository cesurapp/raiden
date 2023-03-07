<?php

namespace Package\SwooleBundle\Runtime;

use Package\SwooleBundle\Runtime\SwooleServer\CronServer;
use Package\SwooleBundle\Runtime\SwooleServer\HttpServer;
use Package\SwooleBundle\Runtime\SwooleServer\TaskServer;
use Package\SwooleBundle\Runtime\SwooleServer\TcpServer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;

class SwooleRunner implements RunnerInterface
{
    public HttpServer $httpServer;
    public TcpServer $tcpServer;
    public CronServer $cronServer;
    public TaskServer $taskServer;

    public function __construct(private readonly HttpKernelInterface $application, private array $options)
    {
        // Load Configuration
        $config = $this->options['project_dir'].'/.server.local.php';
        if (!file_exists($config)) {
            $config = $this->options['project_dir'].'/.server.php';
        }
        $config = (require $config)($this->options);

        // Replace Global Vars
        $config = $this->replaceRuntimeEnv($config);

        // Configure
        $this->options = array_replace_recursive($config, $this->options);
        $this->options['app']['env'] = $_ENV[$this->options['env_var_name']];

        // Configure Watcher
        if (isset($_SERVER['watch'])) {
            $this->options['app']['watch'] = $_SERVER['watch'];
        }
    }

    private function replaceRuntimeEnv(array $options, ?string $parentKey = null): array
    {
        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $options[$key] = $this->replaceRuntimeEnv($option, ($parentKey ? $parentKey.'_' : '').$key);
                continue;
            }

            $searchKey = 'SERVER_'.strtoupper(($parentKey ? $parentKey.'_' : '').$key);
            if (!empty($_ENV[$searchKey])) {
                $options[$key] = $_ENV[$searchKey];
            }
        }

        return $options;
    }

    public function run(): int
    {
        $this->httpServer = new HttpServer($this->application, $this->options);
        $this->tcpServer = new TcpServer($this->httpServer, $this->options);
        $this->taskServer = new TaskServer($this->application, $this->httpServer, $this->options);
        $this->cronServer = new CronServer($this->application, $this->httpServer, $this->options);

        // Manager Start Event
        $this->httpServer->on('managerstart', [$this, 'onStart']);

        return (int) $this->httpServer->start();
    }

    /**
     * Handle Server Start Event.
     */
    public function onStart(HttpServer $server): void
    {
        // Server Information
        $watch = $this->options['app']['watch'] ?? 1;
        if ($watch < 2) {
            $output = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
            $output->definitionList(
                'Swoole HTTP Server Information',
                ['Host' => $this->options['http']['host'].':'.$this->options['http']['port']],
                ['TCP Host' => $this->options['tcp']['host'].':'.$this->options['tcp']['port']],
                ['Worker' => $this->options['http']['settings']['worker_num']],
                ['Task Worker' => $this->options['http']['settings']['task_worker_num']],
                ['Debug' => $this->options['debug'] ? 'True' : 'False'],
                [
                    'Log Level' => match ($this->options['http']['settings']['log_level']) {
                        0 => 'LOG_DEBUG',
                        1 => 'LOG_TRACE',
                        2 => 'LOG_INFO',
                        3 => 'LOG_NOTICE',
                        4 => 'LOG_WARNING',
                        5 => 'LOG_ERROR',
                        6 => 'LOG_NONE',
                        default => '-'
                    },
                ],
                ['Log File' => $this->options['http']['settings']['log_file'] ?? 'STDOUT'],
                ['Environment' => $this->options['app']['env']],
                ['Cron Worker' => $this->options['app']['cron'] ? 'True' : 'False'],
                ['Task Worker' => $this->options['app']['task'] ? 'True' : 'False']
            );
        }
    }
}
