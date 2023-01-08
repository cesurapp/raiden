<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Runtime\SwooleProcess;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'server:start', description: 'Start Swoole Server')]
class ServerStartCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()?->getKernel(); // @phpstan-ignore-line
        $output = new SymfonyStyle($input, $output);
        $server = new SwooleProcess($output, $kernel->getProjectDir());
        $server->start(PHP_BINARY, []);
        sleep(2);

        return Command::SUCCESS;
    }
}
