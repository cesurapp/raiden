<?php

namespace App\Admin\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Global Main Repository.
 */
abstract class BaseRepository extends ServiceEntityRepository
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
}
