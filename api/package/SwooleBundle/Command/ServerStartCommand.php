<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Runtime\SwooleProcess;
use Swoole\Constant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'server:start', description: 'Start Swoole Server')]
class ServerStartCommand extends Command
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

        $options = [
            'http' => [
                'settings' => [
                    Constant::OPTION_PID_FILE => sprintf('%s/var/server.pid', $kernel->getProjectDir()),
                    Constant::OPTION_LOG_FILE => sprintf('%s/var/log/%s_server.log', $kernel->getProjectDir(), $kernel->getEnvironment()),
                ],
            ],
            'app' => ['watch' => 0],
        ];

        // Init Parameters
        if ($input->getOption('host')) {
            $options['http']['host'] = explode(':', $input->getOption('host'))[0];
            $options['http']['port'] = (int) explode(':', $input->getOption('host'))[1];
        }
        if ($input->getOption('cron')) {
            $options['app']['cron'] = $input->getOption('cron');
        }
        if ($input->getOption('task')) {
            $options['app']['task'] = $input->getOption('task');
        }

        $server->start(PHP_BINARY, $options);
        sleep(2);

        return Command::SUCCESS;
    }
}
