<?php

namespace Package\SwooleBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Package\SwooleBundle\Entity\CronLog;

/**
 * @method CronLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CronLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CronLog[]    findAll()
 * @method CronLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CronLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CronLog::class);
    }
}
