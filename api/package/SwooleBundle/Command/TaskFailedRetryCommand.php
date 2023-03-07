<?php

namespace Package\SwooleBundle\Command;

use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use OpenSwoole\Constant;
use Package\SwooleBundle\Entity\FailedTask;
use Psr\Log\NullLogger;
use OpenSwoole\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'task:failed:retry', description: 'Send all failed tasks to queue.')]
class TaskFailedRetryCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = new Client(Constant::SOCK_TCP);

        // Connect Swoole TCP Server
        try {
            $client->connect('0.0.0.0', 9502, 1.5);
            if (!$client->isConnected()) {
                $io->error('Client not connected!');
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->getConfiguration()
            ->setMiddlewares([new Middleware(new NullLogger())]);
        $query = $this->entityManager->createQuery('select f from SwooleBundle:FailedTask f');

        // Send All
        /** @var FailedTask $task */
        foreach ($query->toIterable() as $index => $task) {
            $client->send('taskRetry::'.json_encode([
                    'class' => $task->getTask(),
                    'payload' => $task->getPayload(),
                    'attempt' => $task->getAttempt() + 1,
                ], JSON_THROW_ON_ERROR));
            if ('1' === $client->recv()) {
                $this->entityManager->remove($task);
            }

            usleep(10 * 1000);
            if (0 === $index % 10) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return Command::SUCCESS;
    }
}
