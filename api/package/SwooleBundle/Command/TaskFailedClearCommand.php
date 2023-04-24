<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:failed:clear', description: 'Clean up failed tasks.')]
class TaskFailedClearCommand extends Command
{
    public function __construct(private readonly FailedTaskRepository $failedTaskRepo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Clear All
        $this->failedTaskRepo->createQueryBuilder('q')->delete()->getQuery()->execute();

        $io = new SymfonyStyle($input, $output);
        $io->success('All failed task have been deleted.');

        return Command::SUCCESS;
    }
}
