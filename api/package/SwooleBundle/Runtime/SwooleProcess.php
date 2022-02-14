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
    public function __construct(
        private SymfonyStyle $output,
        private string $rootDir,
        private string $entryPoint = '/bin/index',
    ) {
    }

    /**
     * Start Server.
     */
    public function start(string $phpBinary, array $options): bool
    {
        $server = $this->getServer($options['tcp']['host'].':'.$options['tcp']['port']);
        if ($server->isConnected()) {
            $this->output->warning('Swoole HTTP Server is Running');

            return false;
        }

        // Start
        (new Process(fn (Process $p) => $p->exec($phpBinary, [
            $this->rootDir.$this->entryPoint,
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
        $fsWatch = (new ExecutableFinder())->find('fswatch');
        if (!$fsWatch) {
            $this->output->error('fswatch plugin not found!');

            return;
        }

        // Start File Watcher
        $paths = array_map(fn ($path) => $this->rootDir.$path, $watchDir);
        $process = new SymfonyProcess([$fsWatch, ...$watchExt, '-r', '-e', '.*~', ...$paths], null, null, null, 0);
        $process->start();

        // Watcher Server
        $server = new SymfonyProcess([
            (new PhpExecutableFinder())->find(),
            $this->rootDir.$this->entryPoint,
            '--config='.base64_encode(json_encode($options, JSON_THROW_ON_ERROR)),
        ], null, null, null, 0);
        $server->setTty(true)->start();

        /* @phpstan-ignore-next-line */
        while (true) {
            if ($output = $process->getIncrementalOutput()) {
                $this->output->write('Changed -> '.str_replace($this->rootDir, '', $output));
                Process::kill($server->getPid(), 1);
                $server->stop();
                $server->start(null, ['watch' => random_int(100, 200)]);
            }
            usleep(100 * 1500);
        }
    }

    /**
     * Stop Server.
     */
    public function stop(string $host = '127.0.0.1:9502'): void
    {
        $server = $this->getServer($host);
        if (!$server->isConnected()) {
            $this->output->error('Swoole HTTP server not found!');

            return;
        }

        // Shutdown
        try {
            $server->send('shutdown');
            $server->close();
        } catch (\Exception $exception) {
            $this->output->error($exception->getMessage());
        }

        $this->output->success('Swoole HTTP Server is Stopped!');
    }

    /**
     * Get Current Process ID.
     */
    public function getServer(string $host): Client
    {
        $tcpClient = new Client(SWOOLE_SOCK_TCP);
        try {
            $tcpClient->connect(explode(':', $host)[0], (int) explode(':', $host)[1], 1);
        } catch (\Exception $exception) {
        }

        return $tcpClient;
    }
}
