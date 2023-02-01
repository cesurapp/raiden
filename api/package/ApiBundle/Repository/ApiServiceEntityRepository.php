<?php

namespace Package\ApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Global Main Repository.
 */
abstract class ApiServiceEntityRepository extends ServiceEntityRepository
{
    public function add(object $object, bool $flush = true): self
    {
        $this->getEntityManager()->persist($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $this;
    }

    public function remove(object $object, bool $flush = true): self
    {
        $this->getEntityManager()->remove($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $this;
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function connection(): Connection
    {
        return $this->_em->getConnection();
    }

    public function em(): EntityManagerInterface
    {
        return $this->_em;
    }

    public function countBy(Criteria $criteria): int
    {
        return $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName)->count($criteria);
    }
}
