<?php

namespace App\Admin\Core\Security;

use App\Admin\Core\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param User $user
     */
    public function checkPreAuth(UserInterface $user): void
    {
        // Activate Account
        if (!$user->isApproved()) {
            throw new AccessDeniedException('Account has not been activated');
        }

        // Frozen Account
        if ($user->isFrozen()) {
            throw new AccessDeniedException('The account has been suspended');
        }

        // Check Organization
        if ($user->getOrganization() && $user->getOrganization()->isFrozen()) {
            throw new AccessDeniedException('The organization has been suspended');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
