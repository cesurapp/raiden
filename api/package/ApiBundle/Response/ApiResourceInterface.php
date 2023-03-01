<?php

namespace Package\ApiBundle\Response;

/**
 * Entity API Resource.
 */
interface ApiResourceInterface
{
    public function toArray(object $item): array;

    /**
     * Using Request to Doctrine Filter | DataTable Exporter | Typescript DataTable Schema Generator.
     *
     * Example Resource
     *
     * 'id' => [
     *     'type' => 'string', // TypeScript Type
     *     'filter' => static function (QueryBuilder $builder, string $alias, string $data) { https://app.com?filter[id]=value
     *         $builder->andWhere("$alias.id = :id")->setParameter('id', $data, 'ulid');
     *     },
     *     'table' => [
     *         'label' => 'ID',                     // DataTable Label
     *         'sortable' => true,                  // DataTable Sortable Column
     *         'sortable_default' => true,          // DataTable Default Sortable Column
     *         'sortable_desc' => true,             // DataTable Sortable DESC
     *         'sortable_field' => 'firstName',     // Doctrine Getter Method
     *         'sortable_field' => static fn (QueryBuilder $builder, string $direction) => $builder->orderBy('u.firstName', $direction),
     *         'filter_input' => 'input',           // DataTable Add Filter Input Type
     *         'exporter' => static fn($v) => $v,   // Export Column Template
     *     ]
     * ]
     *
     * Example Two Dimensionel Array
     *
     * 'created_at' => [
     *     'type' => 'string', // TypeScript Type
     *     'filter' => [
     *         'from' => static function (QueryBuilder $builder, string $alias, string $data) {
     *             $builder->andWhere("$alias.createdAt >= :cFrom")->setParameter('cFrom', $data);
     *         },
     *         'to' => static function (QueryBuilder $builder, string $alias, string $data) {
     *             $builder->andWhere("$alias.createdAt <= :cTo")->setParameter('cTo', $data);
     *         },
     *     ],
     *     'table' => [
     *         'label' => 'ID',                 // DataTable Label
     *         'sortable' => true,              // DataTable Sortable Column
     *         'filter_input' => 'daterange',   // DataTable Add Filter Input Type
     *     ]
     * ]
     */
    public function toResource(): array;
}
