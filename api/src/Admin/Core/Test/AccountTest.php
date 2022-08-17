<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class AccountTest extends AbstractWebTestCase
{
    public function testAccountListing(): void
    {
        static::createClient();
        $user = $this->createUser(true)->setType(UserType::ADMIN);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/list');
        $this->isFail();
    }
}
