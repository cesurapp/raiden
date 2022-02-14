<?php

namespace Package\SwooleBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Package\SwooleBundle\Entity\FailedTask;
use Package\SwooleBundle\Task\TaskInterface;

/**
 * @method FailedTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method FailedTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method FailedTask[]    findAll()
 * @method FailedTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FailedTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FailedTask::class);
    }

    /**
     * Get Failed Tasks.
     *
     * @return FailedTask[]
     */
    public function getFailedTask(?FailedTask $nextTask = null, int $limit = 100): array
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'ASC')
            ->setMaxResults($limit);

        if ($nextTask) {
            $query->andWhere('t.createdAt > :next')->setParameter('next', $nextTask->getCreatedAt());
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Resolve Task.
     */
    public function resolveTask(FailedTask $task): void
    {
        $this->_em->remove($task);
        $this->_em->flush();
    }

    /**
     * Create Failed Task.
     */
    public function createTask(array $taskRequest, \Exception $exception): void
    {
        $failedTask = (new FailedTask())
            ->setTask($taskRequest['class'])
            ->setPayload($taskRequest['payload'] ?? [])
            ->setException($exception->getMessage());

        $this->_em->persist($failedTask);
        $this->_em->flush();
    }
}
