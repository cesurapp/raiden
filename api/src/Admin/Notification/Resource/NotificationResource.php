<?php

namespace App\Admin\Notification\Resource;

use App\Admin\Notification\Entity\Notification;
use Package\ApiBundle\Response\ApiResourceInterface;

class NotificationResource implements ApiResourceInterface
{
    /**
     * @param Notification $item
     */
    public function toArray(object $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'type' => $item->getType()->value,
            'title' => $item->getTitle(),
            'message' => $item->getMessage(),
            'readed' => $item->isReaded(),
            'data' => $item->getData(),
            'created_at' => $item->getId()->getDateTime(),
        ];
    }

    public function toResource(): array
    {
        return [
            'id' => ['type' => 'string'],
            'type' => ['type' => 'string'],
            'title' => ['type' => 'string'],
            'message' => ['type' => 'string'],
            'readed' => ['type' => 'boolean'],
            'data' => ['type' => 'array'],
            'created_at' => ['type' => 'datetime'],
        ];
    }
}
