<?php

namespace Package\SwooleBundle\Runtime;

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
        private string $pidFile = '/var/server.pid',
    ) {
    }

    /**
     * Start Server.
     */
    public function start(string $phpBinary, array $options): bool
    {
        if ($this->getPid()) {
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
    public function watch(array $options, array $watchDir = ['/src', '/config', '/package', '/templates'], array $watchExt = ['*.php', '*.yaml', '*.twig']): void
    {
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
        $server = new SymfonyProcess([(new PhpExecutableFinder())->find(),
            $this->rootDir.$this->entryPoint,
            '--config='.base64_encode(json_encode($options, JSON_THROW_ON_ERROR)),
        ], null, null, null, 0);
        $server->setTty(true)->start();

        /* @phpstan-ignore-next-line */
        while (true) {
            if ($output = $process->getIncrementalOutput()) {
                $this->output->write('Changed -> '.str_replace($this->rootDir, '', $output));
                $server->stop();
                $server->start(null, ['watch' => random_int(100, 200)]);
            }
            usleep(100 * 2500);
        }
    }

    /**
     * Stop Server.
     */
    public function stop(bool $force = false): void
    {
        if (!$pid = $this->getPid()) {
            $this->output->error('Swoole HTTP server not found!');

            return;
        }

        // Kill Server
        try {
            if (!Process::kill($pid, $force ? SIGKILL : SIGTERM)) {
                $this->output->error("Warning: Swoole\Process::kill({$pid}) failed, Error: No such process[3]");
            }
        } catch (\Exception $exception) {
            $this->output->error($exception->getMessage());
        }

        // Remove PID
        sleep(1);
        if (!Process::kill($pid, 0)) {
            $this->removePid();
        }

        $this->output->success('Swoole HTTP Server is Stopped!');
    }

    /**
     * Get Current Process ID.
     */
    public function getPid(): ?int
    {
        if (!file_exists($this->pidGetPath())) {
            return null;
        }

        if (!is_readable($this->pidGetPath())) {
            throw new \RuntimeException($this->pidGetPath().' file is not readable!');
        }

        return (int) file_get_contents($this->pidGetPath());
    }

    /**
     * Remove Process ID File.
     */
    public function removePid(): void
    {
        if (file_exists($this->pidGetPath()) && is_writable($this->pidGetPath())) {
            unlink($this->pidGetPath());
        }
    }

    /**
     * Get Process ID File Path.
     */
    public function pidGetPath(): string
    {
        return $this->rootDir.$this->pidFile;
    }
}
