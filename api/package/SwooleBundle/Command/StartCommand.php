<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Server\SwooleServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'server:start', description: 'Start Swoole Server')]
class StartCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('host', InputArgument::OPTIONAL, '0.0.0.0:80', '0.0.0.0:80');
        $this->addArgument('cron', InputArgument::OPTIONAL, 'Enable cron service, default enabled', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleServer($output);
        $server->start($input->getArgument('host'), $input->getArgument('cron'));

        return Command::SUCCESS;
    }
}