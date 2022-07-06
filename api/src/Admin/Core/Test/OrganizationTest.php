<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class OrganizationTest extends AbstractWebTestCase
{
    public function testCreateOrganization(): void
    {
        static::createClient();

        $user = $this->createUser(true);
        $this->save($user);

        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ]);
        $this->isOk();
    }

    public function testFrozenOrganization(): void
    {
        static::createClient();

        $user = $this->createUser(true);
        $user->getOrganization()->setFrozen(true);
        $this->save($user);

        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ]);
        $this->isForbidden();
    }
}
