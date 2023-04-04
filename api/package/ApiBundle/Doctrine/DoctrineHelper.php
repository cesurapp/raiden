<?php

namespace Package\ApiBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;

class DoctrineHelper
{
    /**
     * Clear Same Alias Join.
     */
    public static function setUniqueJoin(QueryBuilder $builder): void
    {
        // Clear Same Alias Joins
        $alias = $builder->getRootAliases()[0];
        $joins = $builder->getDQLPart('join')[$alias] ?? [];
        $aliases = [];

        foreach ($joins as $key => $join) {
            if (in_array($join->getAlias(), $aliases, true)) {
                unset($joins[$key]);
            }

            $aliases[] = $join->getAlias();
        }
        $builder->add('join', [$alias => $joins]);
    }
}
