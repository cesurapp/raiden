<?php

namespace Package\SwooleBundle\Task;

use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use Package\SwooleBundle\Cron\AbstractCronJob;
use Package\SwooleBundle\Entity\FailedTask;
use Psr\Log\NullLogger;
use Swoole\Server;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FailedTaskCron extends AbstractCronJob
{
    public const TIME = '@EveryMinute';

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ParameterBagInterface $bag)
    {
    }

    public function __invoke(): void
    {
        /** @var Server $server */
        $server = $GLOBALS['httpServer'];

        // Disable SQL Logger
        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);
        $attempt = $this->bag->get('swoole.failed_task_attempt');
        $query = $this->entityManager
            ->createQuery('select f from SwooleBundle:FailedTask f WHERE f.attempt < '.$attempt);

        /** @var FailedTask $task */
        foreach ($query->toIterable() as $index => $task) {
            $server->task([
                'class' => $task->getTask(),
                'payload' => $task->getPayload(),
                'attempt' => $task->getAttempt() + 1,
            ]);

            $this->entityManager->remove($task);

            usleep(10 * 1000);
            if (0 === $index % 10) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
