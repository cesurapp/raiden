<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Runtime\SwooleProcess;
use Package\SwooleBundle\Runtime\SwooleRunner;
use Swoole\Constant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'server:start', description: 'Start Swoole Server')]
class StartCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Http host', '0.0.0.0:80');
        $this->addOption('cron', null, InputOption::VALUE_OPTIONAL, 'Enable cron service, default disabled', false);
        $this->addOption('task', null, InputOption::VALUE_OPTIONAL, 'Enable task service, default enabled', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel(); // @phpstan-ignore-line
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleProcess($output, $kernel->getProjectDir());

        $server->start(PHP_BINARY, array_replace_recursive(SwooleRunner::$options, [
            'http' => [
                'host' => explode(':', $input->getOption('host'))[0],
                'port' => (int) explode(':', $input->getOption('host'))[1],
                'settings' => [
                    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
                    Constant::OPTION_TASK_WORKER_NUM => swoole_cpu_num(),
                    Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_ERROR,
                    Constant::OPTION_PID_FILE => $kernel->getProjectDir().'/var/server.pid',
                    Constant::OPTION_LOG_FILE => sprintf('%s/var/log/%s_server.log', $kernel->getProjectDir(), $kernel->getEnvironment()),
                ],
            ],
            'app' => [
                'env' => $kernel->getEnvironment(),
                'watch' => 0,
                'cron' => (int) $input->getOption('cron'),
                'task' => (int) $input->getOption('task'),
            ],
        ]));

        sleep(2);

        return Command::SUCCESS;
    }
}
