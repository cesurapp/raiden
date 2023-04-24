<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\DevicePermission;
use App\Admin\Notification\Service\NotificationPusher;

class DeviceControllerTest extends AbstractWebTestCase
{
    public function testRegister(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy([
            'token' => $token,
        ]);
        $this->assertNotNull($deviceToken);
    }

    public function testRegisterDuplicate(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Register Token
        $token = 'erj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Duplicate
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findBy([
            'token' => $token,
        ]);
        $this->assertNotNull($deviceToken);
        $this->assertCount(1, $deviceToken);
    }

    public function testNotificationTask(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Register Token
        $token = 'krj8S08jxbSkogXwy6-cVq:APA91bfHB26gLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->assertNotNull($deviceToken);

        // Create Notification
        $notification = (new Notification())->setTitle('Title')->setMessage('Message')->setOwner($user);
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);
        $pusher->send($notification);

        // Cleared Device
        $transports = (string) self::getContainer()->get('chatter.transport_factory')->fromString(
            $_SERVER['FIREBASE_DSN']
        );
        if ('null' !== $transports) {
            $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy(['token' => $token]);
            $this->assertNull($deviceToken);
        } else {
            // Check Forwarded
            $this->manager()->refresh($notification);
            $this->assertNotNull($notification->getForwardedAt());
        }
    }

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
        $this->assertGreaterThanOrEqual(1, count($this->json(key: 'data')));

        // Filter
        $data = $this->json(key: 'data');
        $item = end($data);
        $this->client($user)->jsonRequest('GET', '/v1/admin/notification/device?'.http_build_query([
            'filter' => [
                'id' => $item['id'],
                'type' => [$item['type']],
            ],
        ]));
        $this->assertJsonCount(1, 'data');
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
