<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Runtime\SwooleProcess;
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
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'HTTP host 127.0.0.1:8000');
        $this->addOption('cron', null, InputOption::VALUE_OPTIONAL, 'Enable cron service');
        $this->addOption('task', null, InputOption::VALUE_OPTIONAL, 'Enable task service');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel(); // @phpstan-ignore-line
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleProcess($output, $kernel->getProjectDir());

        $options = ['app' => ['watch' => 1]];

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

        $server->watch($options);

        return Command::SUCCESS;
    }
}
