<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Task\TaskWorker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:list', description: 'List Tasks')]
class TaskListCommand extends Command
{
    public function __construct(private TaskWorker $taskWorker)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        if ($tasks = $this->taskWorker->getAll()) {
            $tasks = array_map(static fn ($cron) => get_class($cron), [...$tasks]);
            $output->table(['Task Service'], [[...$tasks]]);
        } else {
            $output->warning('Task not found!');
        }

        return Command::SUCCESS;
    }
}