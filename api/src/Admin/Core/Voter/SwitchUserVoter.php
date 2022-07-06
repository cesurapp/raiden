<?php

namespace App\Admin\Core\Voter;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\CorePermission;
use App\Admin\Core\Enum\UserType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Switch User Voter.
 */
class SwitchUserVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'CAN_SWITCH_USER' === $attribute && $subject instanceof UserInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface || !$subject instanceof UserInterface) {
            return false;
        }

        // Super Admin
        if (UserType::SUPERADMIN === $user->getType()) {
            return true;
        }

        // Check Account Switcher
        if ($user->hasRoles(CorePermission::SWITCH_USER)) {
            return true;
        }

        return false;
    }
}
