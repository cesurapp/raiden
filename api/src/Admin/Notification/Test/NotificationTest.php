<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Service\NotificationPusher;

class NotificationTest extends AbstractWebTestCase
{
    public function testList(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $pusher->create('Test', 'Test Message', user: $user)->send();
        $pusher->create('Test 2', 'Test Message', user: $user)->send();
        $pusher->create('Test 3', 'Test Message', user: $user)->send();
        $pusher->create('Test 4', 'Test Message', user: $user)->send();
        $pusher->create('Test 5', 'Test Message', user: $user)->send();
        $pusher->create('Test 6', 'Test Message', user: $user)->send();
        $pusher->create('Test 7', 'Test Message', user: $user)->send();
        $pusher->create('Test 8', 'Test Message', user: $user)->send();
        $pusher->create('Test 9', 'Test Message', user: $user)->send();
        $pusher->create('Test 10', 'Test Message', user: $user)->send();
        $pusher->create('Test 11', 'Test Message', user: $user)->send();
        $pusher->create('Test 12', 'Test Message', user: $user)->send();

        // List Notification
        $this->client($user)->request('GET', '/v1/main/notification');
        $this->assertJsonCount(10, 'data');

        // Next Page
        $this->client($user)->request('GET', '/v1/main/notification?page=2');
        $this->assertJsonCount(2, 'data');
    }

    public function testUnreadCount(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = $pusher->create('Test', 'Test Message', user: $user);
        $notification->send();
        $this->assertFalse($notification->get()->isReaded());

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

        $notification = $pusher->create('Test', 'Test Message', user: $user);
        $notification->send();
        $this->assertFalse($notification->get()->isReaded());

        // List Notification
        $this->client($user)->request('PUT', '/v1/main/notification/'.$notification->get()->getId()->toBase32());
        $this->isOk();
        $this->assertTrue($notification->get()->isReaded());
    }

    public function testDelete(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $notification = $pusher->create('Test', 'Test Message', user: $user);
        $notification->send();
        $this->assertFalse($notification->get()->isReaded());

        // List Notification
        $this->client($user)->request('DELETE', '/v1/main/notification/'.$notification->get()->getId()->toBase32());
        $this->isOk();
        $this->assertNull($notification->get()->getId());
    }

    public function testReadAll(): void
    {
        self::createClient();

        $user = $this->createUser();
        /** @var NotificationPusher $pusher */
        $pusher = self::getContainer()->get(NotificationPusher::class);

        $pusher->create('Test 6', 'Test Message', user: $user)->send();
        $pusher->create('Test 7', 'Test Message', user: $user)->send();
        $pusher->create('Test 8', 'Test Message', user: $user)->send();

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
