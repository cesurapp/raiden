<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Service\NotificationPusher;

class DeviceTest extends AbstractWebTestCase
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

        // Fail Duplicate
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isFail();

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy([
            'token' => $token,
        ]);
        $this->assertNotNull($deviceToken);
    }

    public function testNotificationTask(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Register Token
        $token = 'krj8S08jxbSkogXwy6-cVq:APA91bfHB26WLonFSnygP6_VJ7Hb6WJKWfb-yZU3oJ3khvJQsupGrq2zwEBF4Ll9-cv2HtF9e2U2_X_7ajlihTB2CEFXSthSSOF9uiFEn5XwJBrBXZqA1HNUq3WNiMrMy0z2A09yulfEB';
        $this->client($user)->jsonRequest('POST', '/v1/main/notification/fcm-register', [
            'token' => $token,
            'device' => 'web',
        ]);
        $this->isOk();

        // Check
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->assertNotNull($deviceToken);

        // Create Notification
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);
        $pusher->send($pusher->create('Title', 'Message', user: $user));

        // Check Device
        $deviceToken = $this->manager()->getRepository(Device::class)->findOneBy(['token' => $token]);
        $this->assertNull($deviceToken);
    }
}
