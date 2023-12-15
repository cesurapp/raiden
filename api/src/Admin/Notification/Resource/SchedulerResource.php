<?php

namespace App\Admin\Notification\Resource;

use App\Admin\Notification\Entity\Scheduler;
use Doctrine\ORM\QueryBuilder;
use Cesurapp\ApiBundle\Response\ApiResourceInterface;
use Symfony\Component\Uid\Ulid;

class SchedulerResource implements ApiResourceInterface
{
    /**
     * @param Scheduler $item
     */
    public function toArray(object $item, mixed $optional = null): array
    {
        return [
            'id' => $item->getId()?->toBase32(),
            'campaign_title' => $item->getCampaignTitle(),
            'persist_notification' => $item->isPersistNotification(),
            'delivered_count' => $item->getDeliveredCount(),
            'failed_count' => $item->getFailedCount(),
            'status' => $item->getStatus()->value,
            'send_at' => $item->getSendAt()?->format(DATE_ATOM),
            'created_at' => $item->getId()?->getDateTime()->format(DATE_ATOM),
            'notification' => (new NotificationResource())->toArray($item->getNotification()),
            'device_filter' => $item->getDeviceFilter(),
        ];
    }

    public function toResource(): array
    {
        return [
            'notification' => ['type' => NotificationResource::class],
            'device_filter' => ['type' => '?object'],
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
            'campaign_title' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.campaignTitle LIKE :title")->setParameter('title', "%$data%");
                },
                'table' => [
                    'label' => 'Campaign Title',
                    'sortable' => false,
                ],
            ],
            'status' => [
                'type' => 'string',
                'filter' => static function (QueryBuilder $builder, string $alias, string $data) {
                    $builder->andWhere("$alias.status = :status")->setParameter('status', $data);
                },
                'table' => [
                    'label' => 'Status',
                    'sortable' => true,
                ],
            ],
            'persist_notification' => [
                'type' => 'boolean',
                'filter' => static function (QueryBuilder $builder, string $alias, $data) {
                    $data = filter_var($data, FILTER_VALIDATE_BOOL);
                    $builder->andWhere("$alias.persistNotification = :persistNotification")->setParameter('persistNotification', $data);
                },
                'table' => [
                    'label' => 'Persistent Notification',
                    'sortable' => true,
                    'filter_input' => 'checkbox',
                ],
            ],
            'delivered_count' => [
                'type' => 'int',
                'filter' => [
                    'min' => static function (QueryBuilder $builder, string $alias, string|int $data) {
                        $builder->andWhere("$alias.deliveredCount >= :dMin")->setParameter('dMin', $data);
                    },
                    'max' => static function (QueryBuilder $builder, string $alias, string|int $data) {
                        $builder->andWhere("$alias.deliveredCount <= :dMax")->setParameter('dMax', $data);
                    },
                ],
                'table' => [
                    'label' => 'Delivered Count',
                    'sortable' => true,
                    'filter_input' => 'range',
                ],
            ],
            'failed_count' => [
                'type' => 'int',
                'filter' => [
                    'min' => static function (QueryBuilder $builder, string $alias, string|int $data) {
                        $builder->andWhere("$alias.failedCount >= :fMin")->setParameter('fMin', $data);
                    },
                    'max' => static function (QueryBuilder $builder, string $alias, string|int $data) {
                        $builder->andWhere("$alias.failedCount <= :fMax")->setParameter('fMax', $data);
                    },
                ],
                'table' => [
                    'label' => 'Failed Count',
                    'sortable' => true,
                    'filter_input' => 'range',
                ],
            ],
            'send_at' => [
                'type' => 'string',
                'filter' => [
                    'from' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.sendAt >= :sFrom")->setParameter('sFrom', $data);
                    },
                    'to' => static function (QueryBuilder $builder, string $alias, string $data) {
                        $builder->andWhere("$alias.sendAt <= :sTo")->setParameter('sTo', $data);
                    },
                ],
                'table' => [
                    'label' => 'Sending Date',
                    'sortable' => true,
                    'filter_input' => 'daterange',
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
