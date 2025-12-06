<?php

namespace App\Admin\Core\Resource;

use App\Admin\Core\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Cesurapp\ApiBundle\Response\ApiResourceInterface;
use Symfony\Component\Uid\Uuid;

class UserResource implements ApiResourceInterface
{
    /**
     * @param User $item
     */
    public function toArray(mixed $item, mixed $optional = null): array
    {
        return [
            'id' => $item->getId()?->toString(),
            'type' => $item->getType()->value,
            'email' => $item->getEmail(),
            'email_approved' => $item->isEmailApproved(),
            'phone' => $item->getPhone(),
            'phone_country' => $item->getPhoneCountry(),
            'phone_approved' => $item->isPhoneApproved(),
            'approved' => $item->isApproved(),
            'frozen' => $item->isFrozen(),
            'roles' => $item->getRoles(),
            'language' => $item->getLanguage(),
            'first_name' => $item->getFirstName(),
            'last_name' => $item->getLastName(),
            'created_at' => $item->getCreatedAt()->format(DATE_ATOM),
            'meta' => $item->getMeta(),
        ];
    }

    public function toResource(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    if (!Uuid::isValid(trim($data))) {
                        throw new \InvalidArgumentException(sprintf('Invalid UUID: "%s".', $data));
                    }

                    $builder->andWhere("$alias.id = :id")->setParameter('id', trim($data), 'uuid');
                },
                'table' => [
                    'label' => 'ID',
                    'sortable' => true,
                    'sortable_default' => true,
                    'sortable_desc' => true,
                    'filter_input' => 'input',
                ],
            ],
            'first_name' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.firstName LIKE :firstName")->setParameter('firstName', "%$data%");
                },
                'table' => [
                    'label' => 'First Name',
                    'sortable' => false,
                    'filter_input' => 'input',
                ],
            ],
            'last_name' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.lastName LIKE :lastName")->setParameter('lastName', "%$data%");
                },
                'table' => [
                    'label' => 'Last Name',
                    'sortable' => false,
                    'filter_input' => 'input',
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
                    $builder->andWhere("$alias.email  LIKE :email")->setParameter('email', "%$data%");
                },
                'table' => [
                    'label' => 'Email',
                    'sortable' => true,
                    'filter_input' => 'input',
                ],
            ],
            'email_approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, $data) {
                    $data = filter_var($data, FILTER_VALIDATE_BOOL);
                    $builder->andWhere("$alias.emailApproved = :emailApproved")->setParameter('emailApproved', $data);
                },
                'table' => [
                    'label' => 'Email Approved',
                    'sortable' => true,
                    'filter_input' => 'checkbox',
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
                    'filter_input' => 'number',
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
                    'filter_input' => 'country',
                ],
            ],
            'phone_approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, $data) {
                    $data = filter_var($data, FILTER_VALIDATE_BOOL);
                    $builder->andWhere("$alias.phoneApproved = :phoneApproved")->setParameter('phoneApproved', $data);
                },
                'table' => [
                    'label' => 'Phone Approved',
                    'sortable' => true,
                    'filter_input' => 'checkbox',
                ],
            ],
            'approved' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, $data) {
                    $data = filter_var($data, FILTER_VALIDATE_BOOL);

                    if (!$data) {
                        $builder
                            ->andWhere("$alias.emailApproved = :approved")
                            ->andWhere("$alias.phoneApproved = :approved")
                            ->setParameter('approved', false);
                    } else {
                        $builder
                            ->orWhere("$alias.emailApproved = :approved")
                            ->orWhere("$alias.phoneApproved = :approved")
                            ->setParameter('approved', false);
                    }
                },
                'table' => [
                    'label' => 'Approved',
                    'sortable' => true,
                    'filter_input' => 'checkbox',
                    'sortable_field' => static function (QueryBuilder $builder, string $alias, string $direction) {
                        $builder
                            ->orderBy("$alias.emailApproved", $direction)
                            ->addOrderBy("$alias.phoneApproved", $direction);
                    },
                ],
            ],
            'frozen' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, $data) {
                    $data = filter_var($data, FILTER_VALIDATE_BOOL);
                    $builder->andWhere("$alias.frozen = :frozen")->setParameter('frozen', $data);
                },
                'table' => [
                    'label' => 'Frozen',
                    'sortable' => true,
                    'filter_input' => 'checkbox',
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
                    'filter_input' => 'language',
                ],
            ],
            'created_at' => [
                'type' => 'string',
                'filter' => [
                    'from' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.createdAt >= :cFrom")->setParameter('cFrom', $data);
                    },
                    'to' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.createdAt <= :cTo")->setParameter('cTo', $data);
                    },
                ],
                'table' => [
                    'label' => 'Created',
                    'sortable' => true,
                    'filter_input' => 'daterange',
                ],
            ],
            'meta' => ['type' => 'array'],
        ];
    }
}
