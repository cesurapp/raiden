<?php

use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Cron\SchedulerCron;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Enum\NotificationStatus;
use App\Admin\Notification\Enum\SchedulerPermission;
use App\Admin\Notification\Enum\SchedulerStatus;
use Package\SwooleBundle\Cron\CronWorker;

class SchedulerControllerTest extends AbstractWebTestCase
{
    public function testList(): void
    {
        static::createClient();
        $user = $this->createAdmin(SchedulerPermission::ROLE_SCHEDULER_LIST);

        // Create Scheduled Notification
        $sn = (new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(false)
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new DateTimeImmutable());
        $this->save($sn);

        // List
        $this->client($user)->jsonRequest('GET', '/v1/admin/scheduler');
        $this->assertJsonCount(1, 'data');

        // Filter
        $id = $this->json(key: 'data')[0]['id'];
        $this->client($user)->jsonRequest(
            'GET',
            '/v1/admin/scheduler?'.http_build_query([
                'filter' => [
                    'id' => $id,
                    'title' => 'Test',
                    'persist_notification' => false,
                    'delivered_count' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    'failed_count' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    'status' => SchedulerStatus::INIT->value,
                    'send_at' => [
                        'from' => (new DateTime('-1 hour'))->format(DATE_ATOM),
                        'to' => (new DateTime('+1 hour'))->format(DATE_ATOM),
                    ],
                ],
            ])
        );
        $this->assertJsonCount(1, 'data');
    }

    public function testCreate(): void
    {
        static::createClient();
        $user = $this->createAdmin(SchedulerPermission::ROLE_SCHEDULER_CREATE);

        $this->client($user)->jsonRequest('POST', '/v1/admin/scheduler', [
            'campaign_title' => 'Campaign',
            'persist_notification' => true,
            'send_at' => (new DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
            'title' => 'Başlık',
            'message' => 'İçerik',
            'status' => NotificationStatus::DANGER,
            'device_filter' => [
                'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                'user.createdAt' => [
                    'from' => (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                    'to' => (new DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                ],
                'user.type' => [UserType::USER],
                'user.frozen' => false,
                'user.language' => 'tr',
                'user.phoneCountry' => 'TR',
            ],
        ]);
        $this->isOk();
    }

    public function testEdit(): void
    {
        static::createClient();
        $user = $this->createAdmin(SchedulerPermission::ROLE_SCHEDULER_EDIT);

        // Create Scheduled Notification
        $sn = (new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(true)
            ->setSendAt(new DateTimeImmutable('+1 hours'))
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new DateTimeImmutable());
        $this->save($sn);

        // Update
        $this->client($user)->jsonRequest('PUT', '/v1/admin/scheduler/'.$sn->getId()->toBase32(), [
            'campaign_title' => 'Campaign',
            'persist_notification' => true,
            'send_at' => (new DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
            'title' => 'Başlık',
            'message' => 'İçerik',
            'status' => NotificationStatus::INFO,
            'device_filter' => [
                'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                'user.createdAt' => [
                    'from' => (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                    'to' => (new DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                ],
                'user.type' => [UserType::USER],
                'user.frozen' => false,
                'user.language' => 'tr',
                'user.phoneCountry' => 'TR',
            ],
        ]);
        $this->isOk();
    }

    public function testCronProcess(): void
    {
        $user = $this->createAdmin(SchedulerPermission::ROLE_SCHEDULER_DELETE);

        // Create Device
        for ($i = 0; $i < 10; ++$i) {
            $this->save(
                (new Device())
                    ->setToken($i.'krj8S08jxbSkas78d6asd87as6ds8da678ogXwy')
                    ->setType(DeviceType::WEB)
                    ->setOwner($user)
            );
        }
        $this->assertGreaterThanOrEqual(10, $this->manager()->getRepository(Device::class)->count([]));

        // Create Scheduled Notification
        $sn = (new Scheduler())
            ->setCampaignTitle('Test')
            ->setStatus(SchedulerStatus::INIT)
            ->setPersistNotification(false)
            ->setSendAt(new DateTimeImmutable('-1 minute'))
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
                    ->addClickAction('https://google.com')
            )
            ->setDeviceFilter([
                'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                'user.createdAt' => [
                    'from' => (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                    'to' => (new DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                ],
                'user.type' => [UserType::ADMIN],
                'user.frozen' => false,
                'user.language' => 'tr',
                'user.phoneCountry' => 'TR',
            ]);
        $this->save($sn);

        // Work Cron Process
        $worker = self::getContainer()->get(CronWorker::class);
        $cron = $worker->get(SchedulerCron::class);
        $cron();

        // Cleared Device
        $transports = (string) self::getContainer()->get('chatter.transport_factory')->fromString(
            $_SERVER['FIREBASE_DSN']
        );
        if ('null' !== $transports) {
            $this->assertEquals(0, $this->manager()->getRepository(Device::class)->count([]));
        } else {
            $this->assertGreaterThanOrEqual(10, $this->manager()->getRepository(Device::class)->count([]));
        }
    }

    public function testDelete(): void
    {
        static::createClient();
        $user = $this->createAdmin(SchedulerPermission::ROLE_SCHEDULER_DELETE);

        // Create Scheduled Notification
        $sn = (new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(false)
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new DateTimeImmutable());
        $this->save($sn);

        // Delete
        $this->client($user)->jsonRequest('DELETE', '/v1/admin/scheduler/'.$sn->getId()->toBase32());
        $this->isOk();
    }
}
