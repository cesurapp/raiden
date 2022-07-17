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
        'type' => 'string',
        'email' => 'string',
        'email_approved' => 'boolean',
        'phone' => 'int',
        'phone_country' => 'string',
        'phone_approved' => 'boolean',
        'approved' => 'boolean',
        'roles' => 'array',
        'language' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'meta' => 'array',
    ])]
    public function toArray(object $item): array
    {
        return [
            'id' => $item->getId()->toBase32(),
            'type' => $item->getType()->value,
            'email' => $item->getEmail(),
            'email_approved' => $item->isEmailApproved(),
            'phone' => $item->getPhone(),
            'phone_country' => $item->getPhoneCountry(),
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
