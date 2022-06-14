<?php

namespace App\Core\Resource;

use App\Core\Entity\User;
use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Thor\Attribute\ThorResource;

class UserResource implements ApiResourceInterface
{
    /**
     * @param User $item
     */
    #[ThorResource(data: [
        'id' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
    ])]
    public function toArray(object|array $item): array
    {
        return [
            'id' => $item->getId()->toRfc4122(),
            'first_name' => $item->getFirstName(),
            'last_name' => $item->getLastName(),
        ];
    }
}
