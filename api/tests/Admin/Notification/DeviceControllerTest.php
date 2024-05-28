<?php

namespace App\Tests\Admin\Notification;

use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\DevicePermission;
use App\Admin\Notification\Enum\NotificationStatus;
use App\Admin\Notification\Service\NotificationPusher;
use App\Tests\Setup\KernelTestCase;

class DeviceControllerTest extends KernelTestCase
{
    public function testRegister(): void
    {
        $user = $this->emSave($this->getUser());

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ])
            ->isOk();

        // Check
        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy([
            'token' => $token,
        ]);
        $this->assertNotNull($deviceToken);
    }

    public function testRegisterDuplicate(): void
    {
        $user = $this->emSave($this->getUser());

        // Register Token
        $token = 'erj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ])
            ->isOk();

        // Duplicate
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ]);

        // Check
        $deviceToken = $this->em()->getRepository(Device::class)->findBy([
            'token' => $token,
        ]);
        $this->assertNotNull($deviceToken);
        $this->assertCount(1, $deviceToken);
    }

    public function testNotificationTask(): void
    {
        $user = $this->emSave($this->getUser());

        // Register Token
        $token = 'krj8S08jxbSkogXwy6-cVq:APA91bfHB26gLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ])
            ->isOk();

        // Check
        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy(['token' => $token]);
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

        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy(['token' => $token]);
        if ('null' !== $transports) {
            $this->assertNull($deviceToken);
        } else {
            $this->assertNotNull($deviceToken);
        }
    }

    public function testList(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(DevicePermission::ROLE_DEVICE_LIST));

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ]);
        $this->isOk();

        // List
        $this->login($user)->jsonRequest('GET', '/v1/admin/notification/device');
        $this->assertGreaterThanOrEqual(1, count($this->getJson('data')));

        // Filter
        $data = $this->getJson('data');
        $item = end($data);
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/notification/device?'.http_build_query([
                'filter' => [
                    'id' => $item['id'],
                    'type' => [$item['type']],
                ],
            ]))
            ->isJsonCount(1, 'data');
    }

    public function testDelete(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(DevicePermission::ROLE_DEVICE_DELETE));

        // Register Token
        $token = 'crj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ])
            ->isOk();

        // Delete
        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/notification/device/'.$deviceToken->getId()->toBase32())
            ->isOk();

        // Check
        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->assertNull($deviceToken);
    }

    public function testSendNotification(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(DevicePermission::ROLE_DEVICE_SEND));

        // Register Token
        $token = 'crj8S08jxbSkog3Xwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/notification/fcm-register', [
                'token' => $token,
                'device' => 'web',
            ])
            ->isOk();

        // Check
        $deviceToken = $this->em()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->assertNotNull($deviceToken);

        // Send
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/notification/device/'.$deviceToken->getId()->toRfc4122(), [
                'title' => 'Başlık',
                'message' => 'İçerik',
                'status' => NotificationStatus::DANGER,
            ])
            ->isOk();
    }
}
