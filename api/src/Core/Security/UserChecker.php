<?php

namespace App\Core\Security;

use App\Core\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param User $user
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        // Activate Account
        if (!$user->isApproved()) {
            throw new CustomUserMessageAuthenticationException('Account has not been activated');
        }

        // Frozen Account
        if ($user->isFrozen()) {
            throw new CustomUserMessageAuthenticationException('The account has been suspended');
        }

        // Check Organization
        if ($user->getOrganization() && $user->getOrganization()->isFrozen()) {
            throw new CustomUserMessageAuthenticationException('The organization has been suspended');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
