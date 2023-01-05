<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Get Current User.
 */
readonly class CurrentUser
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function get(): ?User
    {
        /** @var User|null $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user;
    }
}
