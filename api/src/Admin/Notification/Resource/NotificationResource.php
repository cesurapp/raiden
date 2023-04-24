<?php

namespace App\Admin\Notification\Resource;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\DeviceType;
use Package\ApiBundle\Response\ApiResourceInterface;

class NotificationResource implements ApiResourceInterface
{
    /**
     * @param Notification $item
     */
    public function toArray(object $item, mixed $optional = null): array
    {
        return [
            'id' => $item->getId()?->toBase32(),
            'status' => $item->getStatus()->value,
            'title' => $item->getTitle(),
            'message' => $item->getMessage(),
            'readed' => $item->isReaded(),
            'data' => $item->getData(DeviceType::tryFrom($optional ?? '')),
            'created_at' => $item->getId()?->getDateTime()->format(DATE_ATOM),
        ];
    }

    public function toResource(): array
    {
        return [
            'id' => ['type' => 'string'],
            'status' => ['type' => 'string'],
            'title' => ['type' => 'string'],
            'message' => ['type' => 'string'],
            'readed' => ['type' => 'boolean'],
            'data' => ['type' => 'object'],
            'created_at' => ['type' => 'string'],
        ];
    }
}
