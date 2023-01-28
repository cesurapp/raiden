<?php

namespace Package\ApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * Init Filtered Columns.
     */
    public static function filterDefault(): array
    {
        return [];

        /*return [
            'id' => static function (QueryBuilder $builder, string $alias, mixed $data) {},
            'createdAt' => static function (QueryBuilder $builder, string $alias, mixed $data) {
                if (isset($data['min'])) {}
                if (isset($data['max'])) {}
            },
            'createdAt' => [
                'min' => static function (QueryBuilder $builder, string $alias, mixed $data) {},
                'max' => static function (QueryBuilder $builder, string $alias, mixed $data) {},
            ]
        ];*/
    }

    public function createFilteredQueryBuilder(Request $request, string $filterId = 'default', ?QueryBuilder $builder = null): QueryBuilder
    {
        $builder ??= $this->createQueryBuilder('q');
        $rootAlias = $builder->getRootAliases()[0];

        // Check Filter
        if (!$request->query->has('filter')) {
            return $builder;
        }

        $data = $request->query->all('filter');
        $current = array_intersect_key($this::{'filter'.ucfirst($filterId)}(), $data);
        foreach ($current as $columnId => $filters) {
            if (!is_array($filters)) {
                $filters($builder, $rootAlias, $data[$columnId]);
                continue;
            }

            foreach ($filters as $id => $filter) {
                if (isset($data[$columnId][$id])) {
                    $filter($builder, $rootAlias, $data[$columnId][$id]);
                }
            }
        }

        return $builder;
    }
}
