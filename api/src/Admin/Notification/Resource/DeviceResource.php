<?php

namespace App\Admin\Notification\Resource;

use App\Admin\Notification\Entity\Device;
use Doctrine\ORM\QueryBuilder;
use Cesurapp\ApiBundle\Response\ApiResourceInterface;
use Symfony\Component\Uid\Ulid;

class DeviceResource implements ApiResourceInterface
{
    /**
     * @param Device $item
     */
    public function toArray(mixed $item, mixed $optional = null): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'token' => $item->getToken(),
            'type' => $item->getType()->value,
            'owner_type' => $item->getOwner()->getType()->value,
            'owner' => sprintf(
                '%s <%s>',
                $item->getOwner()->getFullName(),
                $item->getOwner()->getEmail() ?? (string) $item->getOwner()->getPhone()
            ),
            'created_at' => $item->getId()?->getDateTime()->format(DATE_ATOM),
        ];
    }

    public function toResource(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    if (!Ulid::isValid($data)) {
                        throw new \InvalidArgumentException(sprintf('Invalid ULID: "%s".', $data));
                    }

                    $builder->andWhere("$alias.id = :id")->setParameter('id', $data, 'ulid');
                },
                'table' => [
                    'label' => 'ID',
                    'sortable' => true,
                    'sortable_default' => true,
                    'sortable_desc' => true,
                    'filter_input' => 'input',
                ],
            ],
            'type' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, array $data) {
                    $builder->andWhere("$alias.type IN(:type)")->setParameter('type', $data);
                },
                'table' => [
                    'label' => 'Type',
                    'sortable' => true,
                ],
            ],
            'owner_type' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, array $data) {
                    $builder->leftJoin('q.owner', 'o')
                        ->andWhere('o.type IN(:types)')
                        ->setParameter('types', $data);
                },
                'table' => [
                    'label' => 'Owner Type',
                    'sortable' => true,
                    'sortable_field' => static function (QueryBuilder $builder, string $direction) {
                        $builder->leftJoin('q.owner', 'o')->orderBy('o.type', $direction);
                    },
                ],
            ],
            'owner' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->leftJoin('q.owner', 'o');

                    if (is_numeric($data)) {
                        $builder->andWhere('o.phone = :identity')->setParameter('identity', $data);
                    } else {
                        $builder->andWhere('o.email LIKE :identity')->setParameter('identity', "%$data%");
                    }
                },
                'table' => [
                    'label' => 'User',
                    'sortable' => false,
                    'filter_input' => 'input',
                ],
            ],
            'created_at' => [
                'type' => 'string',
                'filter' => [
                    'from' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.id >= :cFrom")->setParameter('cFrom', Ulid::generate(new \DateTime($data)), 'ulid');
                    },
                    'to' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.id <= :cTo")->setParameter('cTo', Ulid::generate(new \DateTime($data)), 'ulid');
                    },
                ],
                'table' => [
                    'label' => 'Created',
                    'sortable' => true,
                    'sortable_field' => 'id',
                    'filter_input' => 'daterange',
                ],
            ],
        ];
    }
}
