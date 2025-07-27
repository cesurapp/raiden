<?php

namespace App\Tests\Admin\Notification;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Service\NotificationPusher;
use App\Tests\Setup\KernelTestCase;

class NotificationControllerTest extends KernelTestCase
{
    public function testList(): void
    {
        $user = $this->emSave($this->getUser());
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $pusher->send((new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 2')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 3')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 4')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 5')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 6')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 7')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 8')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 9')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 10')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 11')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 12')->setMessage('Test Message')->setOwner($user));

        // List Notification
        $this->login($user)
            ->request('GET', '/v1/main/notification/web')
            ->isJsonCount(10, 'data');

        // Next Page
        $this->login($user)
            ->request('GET', '/v1/main/notification/web?page=2')
            ->isJsonCount(2, 'data');
    }

    public function testUnreadCount(): void
    {
        $user = $this->emSave($this->getUser());
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);
        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->login($user)
            ->request('GET', '/v1/main/notification/unread-count')
            ->isOk()
            ->isEquals(1, 'data');
    }

    public function testRead(): void
    {
        $user = $this->emSave($this->getUser());
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);
        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->login($user)
            ->request('PUT', '/v1/main/notification/'.$notification->getId()->toString())
            ->isOk();

        $this->assertTrue($notification->isReaded());
    }

    public function testDelete(): void
    {
        $user = $this->emSave($this->getUser());
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);
        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->login($user)
            ->request('DELETE', '/v1/main/notification/'.$notification->getId()->toString())
            ->isOk();
        $this->assertNull($this->em()->getRepository(Notification::class)->find($notification->getId()));
    }

    public function testReadAll(): void
    {
        $user = $this->emSave($this->getUser());
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $pusher->send((new Notification())->setTitle('Test 6')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 7')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 8')->setMessage('Test Message')->setOwner($user));

        // List Notification
        $this->login($user)
            ->request('POST', '/v1/main/notification/read-all')
            ->isOk();

        // Empty
        $this->assertEmpty($this->em()->getRepository(Notification::class)->findBy([
            'readed' => false,
            'owner' => $user,
        ]));
    }
}
