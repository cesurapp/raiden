<?php

namespace Package\SwooleBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Package\SwooleBundle\Entity\FailedTask;
use Swoole\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:retry-failed', description: 'Send all failed tasks to queue.')]
class FailedTaskRetryCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = new Client(SWOOLE_SOCK_TCP);
        try {
            $client->connect('0.0.0.0', 9502, 1.5);
            if (!$client->isConnected()) {
                $io->error('Client not connected!');
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $query = $this->entityManager->createQuery('select f from SwooleBundle:FailedTask f');
        /** @var FailedTask $task */
        foreach ($query->toIterable() as $task) {
            $client->send('task-retry::'.json_encode([
                'class' => $task->getTask(),
                'payload' => $task->getPayload(),
            ], JSON_THROW_ON_ERROR));

            usleep(10000);
            $this->entityManager->detach($task);
        }

        // Delete All Tasks
        $this->entityManager->getRepository(FailedTask::class)->createQueryBuilder('f')
            ->delete()->getQuery()->execute();

        return Command::SUCCESS;
    }
}
