<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Server\SwooleServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'server:stop', description: 'Stop Swoole Server')]
class StopCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleServer($output);
        $server->stop();

        return Command::SUCCESS;
    }
}