<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Server\SwooleServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'server:watch', description: 'Watch Swoole Server')]
class WatchCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('host', InputArgument::OPTIONAL, '127.0.0.1:8000', '127.0.0.1:8000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleServer($output);
        $server->watch($input->getArgument('host'));

        return Command::SUCCESS;
    }
}