<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\Organization;
use App\Admin\Core\Entity\RefreshToken;
use App\Admin\Core\Entity\User;
use App\Tests\Setup\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testUserConfirm(): void
    {
        $user = $this->emSave(
            $this->getUser()
                ->setEmailApproved(false)
                ->setPhoneApproved(false)
        );

        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isEquals('Account has not been activated', 'message')
            ->isForbidden();
    }

    public function testUserFrozen(): void
    {
        $user = $this->emSave($this->getUser()->setFrozen(true));

        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isEquals('The account has been suspended', 'message')
            ->isForbidden();
    }

    public function testUserDelete(): void
    {
        $user = $this->emSave($this->getUser());
        $this->emRemove($user)->emFlush();
        $this->assertNull($this->em()->find(User::class, $user->getId()->toString()));
    }

    public function testOrganizationDelete(): void
    {
        /** @var User $user */
        $user = $this->emSave($this->getUser(true));
        $userId = $user->getId()->toString();
        $orgId = $user->getOrganization()->getId()->toString();
        $this->em()->clear();

        $this->emRemove($this->em()->find(Organization::class, $orgId))->emFlush();
        $this->assertNull($this->em()->find(User::class, $userId));
    }

    public function testOwnerTrait(): void
    {
        /** @var User $user */
        $user = $this->emSave($this->getUser(true));
        $token = $this->emSave(
            (new RefreshToken())
            ->setOwner($user)
            ->setToken('asdsadsdasas')
            ->setExpiredAt(new \DateTimeImmutable())
        );

        // Fetch ID
        $userId = $user->getId()->toString();
        $orgId = $user->getOrganization()->getId()->toString();
        $tokenId = $token->getId()->toString();
        $this->em()->clear();

        $this->emRemove($this->em()->find(Organization::class, $orgId))->emFlush();

        $this->assertNull($this->em()->find(User::class, $userId));
        $this->assertNull($this->em()->find(RefreshToken::class, $tokenId));
    }
}
