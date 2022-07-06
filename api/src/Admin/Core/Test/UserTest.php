<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class UserTest extends AbstractWebTestCase
{
    public function testUserConfirm(): void
    {
        static::createClient();

        $user = $this->createUser()->setApproved(false);
        $this->save($user);

        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
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
            'password' => '123123',
        ]);
        $this->isForbidden();
    }
}
