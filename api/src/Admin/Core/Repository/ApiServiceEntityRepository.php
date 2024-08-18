<?php

namespace App\Admin\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function sqlQueryBuilder(): QueryBuilder
    {
        return $this->connection()->createQueryBuilder();
    }

    public function rsmQueryBuilder(string $sql, ?array $selectColumns = null): NativeQuery
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->_class->name, 'q');

        // Add Main Entity Fields
        foreach ($this->_class->fieldNames as $colName => $fieldName) {
            if ($selectColumns && !in_array($fieldName, $selectColumns, true)) {
                continue;
            }

            // Add Enum
            if (isset($this->_class->fieldMappings[$fieldName]['enumType'])) {
                $rsm->addEnumResult($colName, $this->_class->fieldMappings[$fieldName]['enumType']);
            }

            $rsm->addFieldResult('q', $colName, $fieldName);
        }

        // Add Relation Entity Fields
        foreach ($this->_class->associationMappings as $map) {
            if ($selectColumns && !in_array($map['fieldName'], $selectColumns, true)) {
                continue;
            }

            $alias = substr($map['fieldName'], 0, 2);
            $rsm->addJoinedEntityResult($map['targetEntity'], $alias, 'q', $map['fieldName']);
            $rsm->addFieldResult($alias, $map['joinColumns'][0]['name'], $map['joinColumns'][0]['referencedColumnName']);
        }

        return $this->em()->createNativeQuery($sql, $rsm);
    }

    /**
     * @template T
     *
     * @param T $object
     *
     * @return T
     */
    public function reload(mixed $object): mixed
    {
        return $this->em()->find(get_class($object), $object->getId()); // @phpstan-ignore-line
    }
}
