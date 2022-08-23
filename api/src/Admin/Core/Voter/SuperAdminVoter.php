<?php

namespace App\Admin\Core\Voter;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return ($token->getUser() instanceof User) && UserType::SUPERADMIN === $token->getUser()->getType();
    }
}
