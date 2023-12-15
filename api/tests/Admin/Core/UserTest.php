<?php

namespace Admin\Core;

use App\Admin\Core\Entity\Organization;
use App\Admin\Core\Entity\RefreshToken;
use App\Admin\Core\Entity\User;
use App\Tests\Setup\AbstractWebTestCase;

class UserTest extends AbstractWebTestCase
{
    public function testUserConfirm(): void
    {
        static::createClient();

        $user = $this->createUser()->setEmailApproved(false)->setPhoneApproved(false);
        $this->save($user);

        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);
        $this->isForbidden();
    }

    public function testUserFrozen(): void
    {
        static::createClient();

        $user = $this->createUser()->setFrozen(true);
        $this->save($user);

        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);
        $this->isForbidden();
    }

    public function testUserDelete(): void
    {
        static::createClient();

        $user = $this->createUser();
        $this->save($user);

        $this->manager()->remove($user);
        $this->manager()->flush();

        $this->assertNull($user->getId());
    }

    public function testOrganizationDelete(): void
    {
        static::createClient();

        $user = $this->createUser(true);
        $this->save($user);
        $userId = $user->getId()->toBase32();
        $orgId = $user->getOrganization()->getId()->toBase32();
        $this->manager()->clear();

        $this->manager()->remove($this->manager()->find(Organization::class, $orgId));
        $this->manager()->flush();

        $this->assertNull($this->manager()->find(User::class, $userId));
    }

    public function testOwnerTrait(): void
    {
        static::createClient();

        $user = $this->createUser(true);
        $token = (new RefreshToken())->setOwner($user)->setToken('asdsadsdasas')->setExpiredAt(new \DateTimeImmutable());
        $this->save($user);
        $this->save($token);

        // Fetch ID
        $userId = $user->getId()->toBase32();
        $orgId = $user->getOrganization()->getId()->toBase32();
        $tokenId = $token->getId()->toBase32();
        $this->manager()->clear();

        $this->manager()->remove($this->manager()->find(Organization::class, $orgId));
        $this->manager()->flush();

        $this->assertNull($this->manager()->find(User::class, $userId));
        $this->assertNull($this->manager()->find(RefreshToken::class, $tokenId));
    }
}
