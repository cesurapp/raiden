<?php

namespace App\Core\Resources;

use App\Core\Entity\UserEntity;
use Package\ApiBundle\Documentation\ApiResource;
use Package\ApiBundle\Response\ApiResourceInterface;

class UserResource implements ApiResourceInterface
{
    #[ApiResource(data: [
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
