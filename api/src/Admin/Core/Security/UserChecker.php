<?php

namespace App\Admin\Core\Security;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Exception\AccountNotActivatedException;
use App\Admin\Core\Exception\AccountSuspendedException;
use App\Admin\Core\Exception\OrganizationSuspendedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
            throw new AccountNotActivatedException();
        }

        // Frozen Account
        if ($user->isFrozen()) {
            throw new AccountSuspendedException();
        }

        // Check Organization
        if ($user->getOrganization() && $user->getOrganization()->isFrozen()) {
            throw new OrganizationSuspendedException();
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
