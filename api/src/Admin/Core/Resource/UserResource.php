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
            'email_approved' => $item->isEmailApproved(),
            'phone' => $item->getPhone(),
            'phone_approved' => $item->isPhoneApproved(),
            'approved' => $item->isApproved(),
            'roles' => $item->getRoles(),
            'language' => $item->getLanguage(),
            'first_name' => $item->getFirstName(),
            'last_name' => $item->getLastName(),
            'meta' => $item->getMeta(),
        ];
    }
}
