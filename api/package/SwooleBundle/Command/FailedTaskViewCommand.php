<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Entity\FailedTask;
use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:view-failed', description: 'Lists failed tasks.')]
class FailedTaskViewCommand extends Command
{
    public function __construct(private FailedTaskRepository $failedTaskRepo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ConsoleSectionOutput $output */
        $output = $output->section(); // @phpstan-ignore-line
        $io = new SymfonyStyle($input, $output);
        $table = $io->createTable();
        $table->setHeaders(['ID', 'Task', 'Exception', 'Payload', 'Created']);
        $total = $this->failedTaskRepo->count([]);
        if (!$total) {
            $io->success('Failed task not found!');
            return Command::SUCCESS;
        }
        $offset = 0;

        while (true) {
            $tasks = $this->failedTaskRepo->getFailedTask()
                ->setFirstResult($offset)->getQuery()->getResult();
            $offset += 10;

            $table
                ->setRows(array_map(static function (FailedTask $task) {
                    return [
                        $task->getId()->toBase32(),
                        $task->getTask(),
                        $task->getException(),
                        json_encode($task->getPayload(), JSON_THROW_ON_ERROR),
                        $task->getCreatedAt()->format('Y-m-d H:i:s'),
                    ];
                }, $tasks))
                ->setFooterTitle("Page: {$offset}/{$total}")
                ->render();

            if ($offset >= $total || !$io->confirm('View Next Page')) {
                break;
            }

            $output->clear(6);
        }

        return Command::SUCCESS;
    }
}
