<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Enum\AccountPermission;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class AccountTest extends AbstractWebTestCase
{
    public function testShowProfile(): void
    {
        static::createClient();
        $user = $this->createUser(true)->setType(UserType::ADMIN);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/profile');
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testEditProfile(): void
    {
    }


    public function testAccountListing(): void
    {
        static::createClient();
        $user = $this->createUser()
            ->setType(UserType::ADMIN)
            ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account');
        $this->isOk();
        $this->assertJsonStructure(['data' => [['id']]]);
    }

    public function testAccountCreate(): void
    {
        static::createClient();
        $user = $this->createUser(true)
            ->setType(UserType::ADMIN)
            ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/'.$user->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testAccountShow(): void
    {
        static::createClient();
        $user = $this->createUser(true)
            ->setType(UserType::ADMIN)
            ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/'.$user->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testAccountEdit(): void
    {
        static::createClient();
        $user = $this->createUser(true)
            ->setType(UserType::ADMIN)
            ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/'.$user->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testAccountDelete(): void
    {
        static::createClient();
        $user = $this->createUser(true)
            ->setType(UserType::ADMIN)
            ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/'.$user->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }
}
