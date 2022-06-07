<?php

namespace App\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

abstract class BaseRepository extends ServiceEntityRepository
{
    public function add(object $object, bool $flush): void
    {
        $this->getEntityManager()->persist($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $object, bool $flush = false): void
    {
        $this->getEntityManager()->remove($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
