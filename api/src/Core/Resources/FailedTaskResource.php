<?php

namespace App\Core\Resources;

use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Thor\Attribute\ThorResource;
use Package\SwooleBundle\Entity\FailedTask;

class FailedTaskResource implements ApiResourceInterface
{
    #[ThorResource(data: [
        'id' => 'string',
        'task' => FailedTask::class,
        'payload' => 'array',
    ])]
    public function toArray(FailedTask $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'task' => $item->getTask(),
            'payload' => $item->getPayload(),
        ];
    }
}
