<?php

namespace Package\SwooleBundle\Runtime;

use Swoole\Client;
use Swoole\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;

class SwooleProcess
{
    private array $options;

    public function __construct(private SymfonyStyle $output, private string $rootDir)
    {
        // Load Configuration
        $config = $rootDir.'/.server.local.php';
        if (!file_exists($config)) {
            $config = $rootDir.'/.server.php';
        }
        $this->options = (require $config)();
    }

    /**
     * Start Server.
     */
    public function start(string $phpBinary, array $options): bool
    {
        $this->options = array_replace_recursive($this->options, $options);

        if ($this->getServer()?->isConnected()) {
            $this->output->warning('Swoole HTTP Server is Running');

            return false;
        }

        // Start
        (new Process(fn (Process $p) => $p->exec($phpBinary, [
            $this->rootDir.$this->options['entrypoint'],
            '--config='.base64_encode(json_encode($options, JSON_THROW_ON_ERROR)),
        ])))->start();

        $this->output->success('Swoole HTTP Server is Started');

        return true;
    }

    /**
     * Start Watch Server.
     */
    public function watch(
        array $options,
        array $watchDir = ['/src', '/config', '/package', '/templates'],
        array $watchExt = ['*.php', '*.yaml', '*.twig']
    ): void {
        // Check fswatch Plugin
        if (!$fsWatch = (new ExecutableFinder())->find('fswatch')) {
            $this->output->error('fswatch plugin not found!');

            return;
        }

        // Start File Watcher
        $paths = array_map(fn ($path) => $this->rootDir.$path, $watchDir);
        $watcher = new SymfonyProcess([$fsWatch, ...$watchExt, '-r', '-e', '.*~', ...$paths], null, null, null, 0);
        $watcher->start();

        // App Server
        $server = new SymfonyProcess([
            (new PhpExecutableFinder())->find(),
            $this->rootDir.$this->options['entrypoint'],
            '--config='.base64_encode(json_encode($options, JSON_THROW_ON_ERROR)),
        ], null, null, null, 0);
        $server->setTty(true)->start();

        /* @phpstan-ignore-next-line */
        while (true) {
            if ($output = $watcher->getIncrementalOutput()) {
                $this->output->write('Changed -> '.str_replace($this->rootDir, '', $output));
                $server->stop();
                $server->start(null, ['watch' => random_int(100, 200)]);
            }
            usleep(100 * 1000);
        }
    }

    /**
     * Stop Server.
     */
    public function stop(?string $tcpHost = null, ?int $tcpPort = null): bool
    {
        $server = $this->getServer($tcpHost, $tcpPort);
        if (!$server || !$server->isConnected()) {
            $this->output->error('Swoole HTTP server not found!');

            return false;
        }

        // Shutdown
        try {
            $server->send('shutdown');
            $server->close();
        } catch (\Exception $exception) {
            $this->output->error($exception->getMessage());
        }

        $this->output->success('Swoole HTTP Server is Stopped!');

        return true;
    }

    /**
     * Get Current Process ID.
     */
    public function getServer(?string $tcpHost = null, ?int $tcpPort = null): ?Client
    {
        $tcpClient = new Client(SWOOLE_SOCK_TCP);
        try {
            $tcpClient->connect($tcpHost ?? $this->options['tcp']['host'], $tcpPort ?? (int) $this->options['tcp']['port'], 1);
        } catch (\Exception $exception) {
            return null;
        }

        return $tcpClient;
    }
}
