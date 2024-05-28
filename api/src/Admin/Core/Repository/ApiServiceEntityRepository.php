<?php

namespace App\Admin\Core\Repository;

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

    public function merge(object $object, bool $flush = true): object
    {
        $columns = (array) $object;
        $class = get_class($object);
        $newObj = $this->getEntityManager()->find($class, $object->getId());

        foreach ($columns as $column => $value) {
            $setter = 'set'.ucfirst(trim(str_replace($class, '', $column)));
            $getter = 'get'.ucfirst(trim(str_replace($class, '', $column)));

            if (method_exists($newObj, $setter) && method_exists($newObj, $getter)) {
                if (in_array($setter, ['setOrganization', 'setOwner'])) {
                    continue;
                }

                $newObj->{$setter}($object->{$getter}());
            }
        }

        $this->add($newObj, $flush);

        return $newObj;
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
