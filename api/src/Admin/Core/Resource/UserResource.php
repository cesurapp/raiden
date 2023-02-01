<?php

namespace App\Admin\Core\Resource;

use App\Admin\Core\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Package\ApiBundle\Response\ApiResourceInterface;

class UserResource implements ApiResourceInterface
{
    /**
     * @param User $item
     */
    public function toArray(object $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'type' => $item->getType()->value,
            'email' => $item->getEmail(),
            'email_approved' => $item->isEmailApproved(),
            'phone' => $item->getPhone(),
            'phone_country' => $item->getPhoneCountry(),
            'phone_approved' => $item->isPhoneApproved(),
            'approved' => $item->isApproved(),
            'roles' => $item->getRoles(),
            'language' => $item->getLanguage(),
            'first_name' => $item->getFirstName(),
            'last_name' => $item->getLastName(),
            'created_at' => $item->getCreatedAt()->format('d/m/y H:i'),
            'meta' => $item->getMeta(),
        ];
    }

    public function toResource(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.id = :id")->setParameter('id', $data, 'ulid');
                },
                'table' => [
                    'label' => 'ID',
                    'sortable' => true,
                ],
            ],
            'type' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.type = :type")->setParameter('type', $data);
                },
                'table' => [
                    'label' => 'Type',
                    'sortable' => true,
                ],
            ],
            'email' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.email = :email")->setParameter('email', $data);
                },
                'table' => [
                    'label' => 'Email',
                    'sortable' => true,
                ],
            ],
            'email_approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, bool $data) {
                    $builder->andWhere("$alias.emailApproved = :emailApproved")->setParameter('emailApproved', $data);
                },
                'table' => [
                    'label' => 'Email Approved',
                    'sortable' => true,
                ],
            ],
            'phone' => [
                'type' => 'int',
                'filter' => static function (QueryBuilder $builder, string $alias, string|int $data) {
                    $builder->andWhere("$alias.phone = :phone")->setParameter('phone', $data);
                },
                'table' => [
                    'label' => 'Phone',
                    'sortable' => true,
                ],
            ],
            'phone_country' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.phoneCountry = :phoneCountry")->setParameter('phoneCountry', $data);
                },
                'table' => [
                    'label' => 'Phone Country',
                    'sortable' => true,
                ],
            ],
            'phone_approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, bool $data) {
                    $builder->andWhere("$alias.phoneApproved = :phoneApproved")->setParameter('phoneApproved', $data);
                },
                'table' => [
                    'label' => 'Phone Approved',
                    'sortable' => true,
                ],
            ],
            'approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, bool $data) {
                    $builder->andWhere("$alias.approved = :approved")->setParameter('approved', $data);
                },
                'table' => [
                    'label' => 'Approved',
                    'sortable' => true,
                ],
            ],
            'roles' => [
                'type' => 'array',
                'table' => [
                    'label' => 'Roles',
                    'sortable' => false,
                ],
            ],
            'language' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.language = :language")->setParameter('language', $data);
                },
                'table' => [
                    'label' => 'Language',
                    'sortable' => true,
                ],
            ],
            'first_name' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.firstName LIKE(:firstName)")->setParameter('firstName', "%$data%");
                },
                'table' => [
                    'label' => 'First Name',
                    'sortable' => false,
                ],
            ],
            'last_name' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.lastName LIKE(:lastName)")->setParameter('lastName', "%$data%");
                },
                'table' => [
                    'label' => 'Last Name',
                    'sortable' => false,
                ],
            ],
            'created_at' => [
                'type' => 'string',
                'filter' => [
                    'min' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.createdAt >= :cMin")->setParameter('cMin', $data);
                    },
                    'max' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.createdAt <= :cMax")->setParameter('cMax', $data);
                    },
                ],
                'table' => [
                    'label' => 'Created',
                    'sortable' => true,
                ],
            ],
            'meta' => ['type' => 'array'],
        ];
    }
}
