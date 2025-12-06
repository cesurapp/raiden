<?php

namespace App\Admin\Core\Voter;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\CorePermission;
use App\Admin\Core\Permission\UserType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
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

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User || !$subject instanceof User) {
            return false;
        }

        // Super Admin
        if ($user->hasType(UserType::SUPERADMIN)) {
            return true;
        }

        // Disable Super Admin to Admin
        if ($subject->hasType(UserType::SUPERADMIN)) {
            return false;
        }

        // Check Account Switcher
        if ($user->hasRoles(CorePermission::SWITCH_USER)) {
            return true;
        }

        return false;
    }
}
