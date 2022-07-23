<?php

namespace App\Admin\Notification\Resource;

use App\Admin\Notification\Entity\Notification;
use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Thor\Attribute\ThorResource;

class NotificationResource implements ApiResourceInterface
{
    /**
     * @param Notification $item
     */
    #[ThorResource(data: [
        'id' => 'string',
        'type' => 'string',
        'title' => 'string',
        'message' => 'string',
        'read' => 'boolean',
        'createdAt' => 'datetime',
    ])]
    public function toArray(object $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'type' => $item->getType()->value,
            'title' => $item->getTitle(),
            'message' => $item->getMessage(),
            'read' => $item->isRead(),
            'createdAt' => $item->getId()->getDateTime(),
        ];
    }
}
