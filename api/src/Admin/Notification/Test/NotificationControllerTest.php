<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Service\NotificationPusher;

class NotificationControllerTest extends AbstractWebTestCase
{
    public function testList(): void
    {
        self::createClient();

        $user = $this->createUser();
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
        $this->client($user)->request('GET', '/v1/main/notification/web');
        $this->assertJsonCount(10, 'data');

        // Next Page
        $this->client($user)->request('GET', '/v1/main/notification/web?page=2');
        $this->assertJsonCount(2, 'data');
    }

    public function testUnreadCount(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);

        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->client($user)->request('GET', '/v1/main/notification/unread-count');
        $this->isOk();
        $this->assertEquals(1, $this->json(null, 'data'));
    }

    public function testRead(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);
        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->client($user)->request('PUT', '/v1/main/notification/'.$notification->getId()->toBase32());
        $this->isOk();
        $this->assertTrue($notification->isReaded());
    }

    public function testDelete(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = (new Notification())->setTitle('Test')->setMessage('Test Message')->setOwner($user);
        $pusher->send($notification);
        $this->assertFalse($notification->isReaded());

        // List Notification
        $this->client($user)->request('DELETE', '/v1/main/notification/'.$notification->getId()->toBase32());
        $this->isOk();
        $this->assertNull($notification->getId());
    }

    public function testReadAll(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $pusher->send((new Notification())->setTitle('Test 6')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 7')->setMessage('Test Message')->setOwner($user));
        $pusher->send((new Notification())->setTitle('Test 8')->setMessage('Test Message')->setOwner($user));

        // List Notification
        $this->client($user)->request('POST', '/v1/main/notification/read-all');
        $this->isOk();

        // Empty
        $this->assertEmpty($this->manager()->getRepository(Notification::class)->findBy([
            'readed' => false,
            'owner' => $user,
        ]));
    }
}
