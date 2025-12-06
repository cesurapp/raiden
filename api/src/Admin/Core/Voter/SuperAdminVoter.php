<?php

namespace App\Admin\Core\Voter;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * SuperAdmin|Root User Voter.
 */
class SuperAdminVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [
            'IS_AUTHENTICATED_ANONYMOUSLY',
            'IS_AUTHENTICATED_FULLY',
            'IS_AUTHENTICATED_REMEMBERED',
            'ROLE_ALLOWED_TO_SWITCH',
            'IS_IMPERSONATOR',
        ], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return ($token->getUser() instanceof User) && $token->getUser()->hasType(UserType::SUPERADMIN);
    }
}
