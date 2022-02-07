<?php

namespace Package\SwooleBundle\Server;

use Package\ApiBundle\Utils\Util;
use Swoole\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;

class SwooleServer
{
    public function __construct(private SymfonyStyle $output)
    {
    }

    /**
     * Start Server
     */
    public function start(string $host, bool $cron): void
    {
        if ($this->getPid()) {
            $this->output->warning('Swoole HTTP Server is Running');
            return;
        }

        // Start
        $server = new Process(function (Process $process) use ($host, $cron) {
            $process->exec(PHP_BINARY, [
                Util::rootDir('public/index.php'),
                "--env=" . $_ENV['APP_ENV'],
                "--host=" . $host,
                "--worker=" . swoole_cpu_num() * 2,
                "--task_worker=" . swoole_cpu_num() / 2,
                "--cron=" . $cron
            ]);
        });
        $server->start();

        sleep(1);

        $this->output->success('Swoole HTTP Server is Started');
    }

    /**
     * Stop Server
     */
    public function stop(): void
    {
        if (!$pid = $this->getPid()) {
            $this->output->error('Swoole HTTP Server Not Found!');
            return;
        }

        // Kill Server
        try {
            if (!Process::kill($pid, SIGTERM)) {
                $this->output->error("Warning: Swoole\Process::kill(): kill({$pid}, 15) failed, Error: No such process[3]");
                $this->removePid();
                return;
            }
        } catch (\Exception $exception) {
            $this->output->error($exception->getMessage());
            $this->removePid();
            return;
        }

        $this->output->success('Swoole HTTP Server is Stopped!');
    }

    /**
     * Start Watch Server
     */
    public function watch(string $host): void
    {
        // Start File Watcher
        $process = new SymfonyProcess([
            'fswatch', '*.php', '*.yaml', '*.twig', '-r', '-e', '.*~',
            Util::rootDir('src'),
            Util::rootDir('config'),
            Util::rootDir('package'),
            Util::rootDir('public'),
            Util::rootDir('templates')
        ], null, null, null, 0);
        $process->start();

        // Watcher Server
        $server = new SymfonyProcess([
            (new PhpExecutableFinder())->find(),
            Util::rootDir('public/index.php'),
            "--env=" . $_ENV['APP_ENV'],
            "--host=" . $host,
            "--worker=2",
            "--task_worker=2",
            "--watch=1"
        ], null, ['SHELL_VERBOSITY' => 3], null, 0);
        $server->setTty(true)->start();

        /** @phpstan-ignore-next-line */
        while (true) {
            if ($output = $process->getIncrementalOutput()) {
                $this->output->write($output);
                $server->stop();
                $server->start(null, ['watch' => random_int(100, 200)]);
            }
        }
    }

    /**
     * Get Current Process ID
     */
    public function getPid(): ?int
    {
        if (!file_exists($this->pidGetPath())) {
            return null;
        }

        if (!is_readable($this->pidGetPath())) {
            throw new \RuntimeException($this->pidGetPath() . ' file is not readable!');
        }

        return (int)file_get_contents($this->pidGetPath());
    }

    /**
     * Remove Process ID File
     */
    private function removePid(): void
    {
        if (file_exists($this->pidGetPath()) && is_writable($this->pidGetPath())) {
            unlink($this->pidGetPath());
        }
    }

    /**
     * Get Process ID File Path
     */
    private function pidGetPath(): string
    {
        return Util::rootDir("var/swoole_server.pid");
    }
}