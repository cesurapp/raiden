<?php

namespace Package\ApiBundle\Response\Traits;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Package\ApiBundle\Doctrine\DoctrineHelper;
use Symfony\Component\HttpFoundation\Request;

trait DoctrineFilterTrait
{
    /**
     * Filter QueryBuilder.
     */
    private function filterQueryBuilder(QueryBuilder|Query $builder, Request $request, array $resource): void
    {
        $this->sortResult($builder, $request, $resource);

        if (!$request->query->has('filter')) {
            return;
        }

        $alias = $builder->getRootAliases()[0];
        $data = $request->query->all('filter');
        $current = array_intersect_key($this->getFilters($resource), $data);

        foreach ($current as $columnId => $config) {
            if (!isset($config['filter'])) {
                continue;
            }

            if (!is_array($config['filter'])) {
                $config['filter']($builder, $alias, $data[$columnId]);
                continue;
            }

            foreach ($config['filter'] as $id => $filter) {
                if (isset($data[$columnId][$id])) {
                    $filter($builder, $alias, $data[$columnId][$id]);
                }
            }
        }

        DoctrineHelper::setUniqueJoin($builder);
    }

    /**
     * Sort QueryBuilder.
     *
     * sort=ASC
     * sort-by=id
     */
    private function sortResult(QueryBuilder|Query $builder, Request $request, array $resource): void
    {
        $sortBy = $request->query->get('sort_by');
        $resource = $resource[$sortBy]['table'] ?? [];

        if (!$sortBy || empty($resource['sortable'])) {
            return;
        }

        // Generate Direction
        $direction = match (strtoupper($request->query->get('sort', ''))) {
            'ASC' => 'ASC',
            default => 'DESC'
        };

        if (is_callable($resource['sortable_field'] ?? '')) {
            $resource['sortable_field']($builder, $direction);

            return;
        }

        $sortBy = $resource['sortable_field'] ?? $sortBy;
        $sortBy = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $sortBy))));

        $builder->orderBy($builder->getRootAliases()[0].'.'.$sortBy, $direction);
    }

    private function getFilters(array $resource): array
    {
        return array_filter($resource, static fn ($v) => isset($v['filter']));
    }
}
