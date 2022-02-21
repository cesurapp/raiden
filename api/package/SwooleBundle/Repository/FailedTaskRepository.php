<?php

namespace Package\SwooleBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Package\SwooleBundle\Entity\FailedTask;

/**
 * @method FailedTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method FailedTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method FailedTask[]    findAll()
 * @method FailedTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<FailedTask>
 */
class FailedTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FailedTask::class);
    }

    /**
     * Get Failed Tasks.
     */
    public function getFailedTask(?FailedTask $nextTask = null, int $limit = 10): QueryBuilder
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->setMaxResults($limit);

        if ($nextTask) {
            $query->andWhere('t.id < :next')->setParameter('next', $nextTask->getId(), 'ulid');
        }

        return $query;
    }

    /**
     * Resolve Task.
     */
    public function resolveTask(FailedTask $task): void
    {
        $this->_em->remove($task);
        $this->_em->flush();
        $this->_em->detach($task);
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
        $this->_em->detach($failedTask);
    }
}
