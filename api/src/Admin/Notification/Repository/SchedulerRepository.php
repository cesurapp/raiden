<?php

namespace App\Admin\Notification\Repository;

use App\Admin\Core\Repository\ApiServiceEntityRepository;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\SchedulerStatus;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Scheduler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scheduler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scheduler[]    findAll()
 * @method Scheduler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchedulerRepository extends ApiServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scheduler::class);
    }

    /**
     * Get Listing Query.
     */
    public function list(): QueryBuilder
    {
        return $this->createQueryBuilder('q')->orderBy('q.id', 'DESC');
    }

    /**
     * Remove Notification.
     */
    public function delete(Scheduler $notification): void
    {
        $this->remove($notification);
    }

    public function getProcessed(): QueryBuilder
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.status = :status')->setParameter('status', SchedulerStatus::INIT)
            ->andWhere('q.sendAt <= :sendAt')->setParameter('sendAt', new \DateTimeImmutable());
    }
}
