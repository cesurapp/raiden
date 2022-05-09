<?php

namespace App\Core\Resources;

use App\Core\Entity\UserEntity;
use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Thor\Attribute\ThorResource;

class UserResource implements ApiResourceInterface
{
    #[ThorResource(data: [
        'id' => '?string',
        'name' => '?string',
        'data' => [
            'ke' => 'string',
            'asd' => 'int',
        ],
    ])]
    public function toArray(UserEntity $item): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
        ];
    }
}
