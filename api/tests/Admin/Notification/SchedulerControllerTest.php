<?php

namespace App\Tests\Admin\Notification;

use App\Admin\Core\Enum\UserType;
use App\Admin\Notification\Cron\SchedulerCron;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Enum\NotificationStatus;
use App\Admin\Notification\Enum\SchedulerPermission;
use App\Admin\Notification\Enum\SchedulerStatus;
use App\Tests\Setup\KernelTestCase;
use Cesurapp\SwooleBundle\Cron\CronWorker;

class SchedulerControllerTest extends KernelTestCase
{
    public function testList(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(SchedulerPermission::ROLE_SCHEDULER_LIST));

        // Create Scheduled Notification
        $this->emSave((new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(false)
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new \DateTimeImmutable()));

        // List
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/scheduler')
            ->isJsonCount(1, 'data');

        // Filter
        $id = $this->getJson('data.0.id');
        $this->login($user)
            ->jsonRequest(
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
                            'from' => (new \DateTime('-1 hour'))->format(DATE_ATOM),
                            'to' => (new \DateTime('+1 hour'))->format(DATE_ATOM),
                        ],
                    ],
                ])
            )
            ->isJsonCount(1, 'data');
    }

    public function testCreate(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(SchedulerPermission::ROLE_SCHEDULER_CREATE));
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/scheduler', [
                'campaign_title' => 'Campaign',
                'persist_notification' => true,
                'send_at' => (new \DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                'title' => 'Başlık',
                'message' => 'İçerik',
                'status' => NotificationStatus::DANGER,
                'device_filter' => [
                    'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                    'user.createdAt' => [
                        'from' => (new \DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                        'to' => (new \DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                    ],
                    'user.type' => [UserType::USER],
                    'user.frozen' => false,
                    'user.language' => 'tr',
                    'user.phoneCountry' => 'TR',
                ],
            ])
            ->isOk();
    }

    public function testEdit(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(SchedulerPermission::ROLE_SCHEDULER_EDIT));

        // Create Scheduled Notification
        $sn = $this->emSave((new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(true)
            ->setSendAt(new \DateTimeImmutable('+1 hours'))
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new \DateTimeImmutable()));

        // Update
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/scheduler/'.$sn->getId()->toBase32(), [
                'campaign_title' => 'Campaign',
                'persist_notification' => true,
                'send_at' => (new \DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                'title' => 'Başlık',
                'message' => 'İçerik',
                'status' => NotificationStatus::INFO,
                'device_filter' => [
                    'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                    'user.createdAt' => [
                        'from' => (new \DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                        'to' => (new \DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                    ],
                    'user.type' => [UserType::USER],
                    'user.frozen' => false,
                    'user.language' => 'tr',
                    'user.phoneCountry' => 'TR',
                ],
            ])
            ->isOk();
    }

    public function testCronProcess(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Create Device
        for ($i = 0; $i < 10; ++$i) {
            $this->emPersist(
                (new Device())
                    ->setToken($i.'krj8S08jxbSkas78d6asd87as6ds8da678ogXwy')
                    ->setType(DeviceType::WEB)
                    ->setOwner($user)
            );
        }
        $this->emFlush();
        $this->assertGreaterThanOrEqual(10, $this->em()->getRepository(Device::class)->count(['owner' => $user]));

        // Create Scheduled Notification
        $sn = (new Scheduler())
            ->setCampaignTitle('Test')
            ->setStatus(SchedulerStatus::INIT)
            ->setPersistNotification(true)
            ->setSendAt(new \DateTimeImmutable('-1 minute'))
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
                    ->addClickAction('https://google.com')
            )
            ->setDeviceFilter([
                'device.type' => [DeviceType::WEB, DeviceType::ANDROID],
                'user.createdAt' => [
                    'from' => (new \DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
                    'to' => (new \DateTimeImmutable('+1 hour'))->format(DATE_ATOM),
                ],
                'user.type' => [UserType::ADMIN],
                'user.frozen' => false,
                'user.language' => 'tr',
                'user.phoneCountry' => 'TR',
            ]);
        $this->emSave($sn);

        // Work Cron Process
        $worker = self::getContainer()->get(CronWorker::class);
        $cron = $worker->get(SchedulerCron::class);
        $cron();

        // Cleared Device
        $transports = (string) self::getContainer()->get('chatter.transport_factory')->fromString(
            $_SERVER['FIREBASE_DSN']
        );
        if ('null' !== $transports) {
            $this->assertEquals(0, $this->em()->getRepository(Device::class)->count([]));
        } else {
            // Check Persistent
            $this->assertEquals(1, $this->em()->getRepository(Notification::class)->count(['owner' => $user]));
            $this->assertGreaterThanOrEqual(10, $this->em()->getRepository(Device::class)->count([]));
        }
    }

    public function testDelete(): void
    {
        $user = $this->emSave($this->getAdmin()->addRoles(SchedulerPermission::ROLE_SCHEDULER_DELETE));

        // Create Scheduled Notification
        $sn = $this->emSave((new Scheduler())
            ->setCampaignTitle('Test')
            ->setPersistNotification(false)
            ->setNotification(
                (new Notification())
                    ->setTitle('Title')
                    ->setMessage('Message')
            )
            ->setSendAt(new \DateTimeImmutable()));

        // Delete
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/scheduler/'.$sn->getId()->toBase32())
            ->isOk();
    }
}
