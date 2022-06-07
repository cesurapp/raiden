<?php

namespace App\Core\Security;

use App\Core\Entity\User;
use App\Core\Enum\UserType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RootVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array(
            $attribute,
            [
                'IS_AUTHENTICATED_ANONYMOUSLY',
                'IS_AUTHENTICATED_FULLY',
                'IS_AUTHENTICATED_REMEMBERED',
                'ROLE_ALLOWED_TO_SWITCH',
                'IS_IMPERSONATOR',
            ],
            true
        )) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return ($token->getUser() instanceof User) && UserType::ROOT === $token->getUser()->getType();
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, User::class, true);
    }
}
