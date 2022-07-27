<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Device;

class DeviceTest extends AbstractWebTestCase
{
    public function testRegister(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Register Token
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
           'token' => 'asdjsadkjasdhasjdhaski',
           'device' => 'web',
        ]);
        $this->isOk();

        // Check
        $token = $this->manager()->getRepository(Device::class)->findOneBy([
            'token' => 'asdjsadkjasdhasjdhaski',
        ]);
        $this->assertNotNull($token);
    }
}
