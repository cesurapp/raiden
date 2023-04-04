<?php

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Enum\DevicePermission;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Task\NotificationTask;

class DeviceManagerTest extends AbstractWebTestCase
{
    public function testList(): void
    {
        static::createClient();
        $user = $this->createAdmin(DevicePermission::ROLE_DEVICE_LIST);

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // List
        $this->client($user)->jsonRequest('GET', '/v1/admin/notification/device');
        $this->assertJsonCount(1, 'data');

        // Filter
        $id = $this->json(key: 'data')[0]['id'];
        $this->client($user)->jsonRequest('GET', '/v1/admin/notification/device?'.http_build_query([
            'filter' => [
                'id' => $id,
                'type' => [DeviceType::WEB->value],
                'owner' => $user->getEmail(),
            ],
        ]));
        $this->assertJsonCount(1, 'data');
    }

    public function testSend(): void
    {
        static::createClient();
        $user = $this->createAdmin(DevicePermission::ROLE_DEVICE_SEND);

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Filter
        $this->mockTaskWorker(function ($payload) {
            $this->assertSame(NotificationTask::class, $payload['class']);
        });
        $device = $this->manager()->getRepository(Device::class)->findAll()[0];
        $this->client($user)->jsonRequest('POST', '/v1/admin/notification/device/'.$device->getId()->toBase32(), [
            'title' => 'Başlık',
            'message' => 'Gövde',
        ]);
        $this->isOk();
    }

    public function testDelete(): void
    {
        static::createClient();
        $user = $this->createAdmin(DevicePermission::ROLE_DEVICE_DELETE);

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Delete
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy([
            'token' => $token,
        ]);
        $this->client($user)->jsonRequest('DELETE', '/v1/admin/notification/device/'.$deviceToken->getId()->toBase32());
        $this->isOk();

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy([
            'token' => $token,
        ]);
        $this->assertNull($deviceToken);
    }
}
