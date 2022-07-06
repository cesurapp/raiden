<?php

namespace App\Admin\Core\Resource;

use App\Admin\Core\Entity\User;
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
    public function toArray(object $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'type' => $item->getType()->value,
            'email' => $item->getEmail(),
            'roles' => $item->getRoles(),
            'language' => $item->getLanguage(),
            'phone' => $item->getPhone(),
            'approved' => $item->isApproved(),
            'first_name' => $item->getFirstName(),
            'last_name' => $item->getLastName(),
        ];
    }
}
