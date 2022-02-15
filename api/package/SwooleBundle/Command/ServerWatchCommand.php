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

#[AsCommand(name: 'server:watch', description: 'Watch Swoole Server')]
class ServerWatchCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Http host', '0.0.0.0:8000');
        $this->addOption('cron', null, InputOption::VALUE_OPTIONAL, 'Enable cron service, default disabled', false);
        $this->addOption('task', null, InputOption::VALUE_OPTIONAL, 'Enable task service, default enabled', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel(); // @phpstan-ignore-line
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleProcess($output, $kernel->getProjectDir());

        $server->watch(array_replace_recursive(SwooleRunner::$options, [
            'http' => [
                'host' => explode(':', $input->getOption('host'))[0],
                'port' => (int) explode(':', $input->getOption('host'))[1],
                'settings' => [
                    Constant::OPTION_WORKER_NUM => 1,
                    Constant::OPTION_TASK_WORKER_NUM => 1,
                    Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_DEBUG,
                    Constant::OPTION_DOCUMENT_ROOT => $kernel->getProjectDir().'/bin',
                    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
                ],
            ],
            'app' => [
                'env' => $kernel->getEnvironment(),
                'watch' => 1,
                'cron' => (int) $input->getOption('cron'),
                'task' => (int) $input->getOption('task'),
            ],
        ]));

        return Command::SUCCESS;
    }
}
