<?php

namespace App\Tests\Admin\Core;

use App\Tests\Setup\KernelTestCase;

class OrganizationTest extends KernelTestCase
{
    public function testCreateOrganization(): void
    {
        $user = $this->emSave($this->getUser(true));

        $this->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);
        $this->isOk();
    }

    public function testFrozenOrganization(): void
    {
        $user = $this->emSave($this->getUser(true));
        $this->emSave($user->getOrganization()->setFrozen(true));

        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isEquals('The organization has been suspended', 'message')
            ->isForbidden();
    }
}
