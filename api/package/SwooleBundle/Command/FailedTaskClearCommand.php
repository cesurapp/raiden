<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:clear-failed', description: 'Clean up failed tasks.')]
class FailedTaskClearCommand extends Command
{
    public function __construct(private FailedTaskRepository $failedTaskRepo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->failedTaskRepo->createQueryBuilder('t')
            ->delete()->getQuery()->execute();

        $io = new SymfonyStyle($input, $output);
        $io->success('All failed task have been deleted.');

        return Command::SUCCESS;
    }
}
