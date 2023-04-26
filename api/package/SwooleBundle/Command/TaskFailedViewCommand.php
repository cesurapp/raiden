<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Entity\FailedTask;
use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:failed:view', description: 'Lists failed tasks.')]
class TaskFailedViewCommand extends Command
{
    public function __construct(private readonly FailedTaskRepository $failedTaskRepo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = method_exists($output, 'section') ? $output->section() : $output;
        $io = new SymfonyStyle($input, $output);

        // Find Total
        if (!$total = $this->failedTaskRepo->count([])) {
            $io->success('Failed task not found!');

            return Command::SUCCESS;
        }

        // Create Table
        $table = $io->createTable();
        $table->setHeaders(['ID', 'Task', 'Exception', 'Payload', 'Created']);

        // View
        $offset = 0;
        while (true) {
            $tasks = $this->failedTaskRepo->getFailedTask()->setFirstResult($offset)->getQuery()->getResult();
            $offset += 10;

            $table
                ->setRows(array_map(static fn (FailedTask $task) => [
                    $task->getId()?->toBase32(),
                    $task->getTask(),
                    $task->getException(),
                    json_encode($task->getPayload(), JSON_THROW_ON_ERROR),
                    $task->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Istanbul'))->format('d/m/Y H:i:s'),
                ], $tasks))
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
