<?php

namespace App\Admin\Notification\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;
use App\Admin\Notification\Entity\Notification;

class NotificationTest extends AbstractWebTestCase
{
    public function testList(): void
    {
        self::createClient();

        $user = $this->createUser();
        $repo = $this->manager()->getRepository(Notification::class);

        $repo->add($repo->create($user, 'Test', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 2', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 3', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 4', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 5', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 6', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 7', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 8', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 9', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 10', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 11', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 12', 'Test Message'));

        // List Notification
        $this->client($user)->request('GET', '/v1/main/notification');
        $this->assertJsonCount(10, 'data');

        // Next Page
        $this->client($user)->request('GET', '/v1/main/notification?page=2');
        $this->assertJsonCount(2, 'data');
    }

    public function testRead(): void
    {
        self::createClient();

        $user = $this->createUser();
        $repo = $this->manager()->getRepository(Notification::class);

        $notification = $repo->create($user, 'Test', 'Test Message');
        $repo->add($notification);
        $this->assertFalse($notification->isRead());

        // List Notification
        $this->client($user)->request('PUT', '/v1/main/notification/'.$notification->getId()->toBase32());
        $this->isOk();
        $this->assertTrue($notification->isRead());
    }

    public function testDelete(): void
    {
        self::createClient();

        $user = $this->createUser();
        $repo = $this->manager()->getRepository(Notification::class);

        $notification = $repo->create($user, 'Test', 'Test Message');
        $repo->add($notification);
        $this->assertFalse($notification->isRead());

        // List Notification
        $this->client($user)->request('DELETE', '/v1/main/notification/'.$notification->getId()->toBase32());
        $this->isOk();
        $this->assertNull($notification->getId());
    }

    public function testReadAll(): void
    {
        self::createClient();

        $user = $this->createUser();
        $repo = $this->manager()->getRepository(Notification::class);

        $repo->add($repo->create($user, 'Test 6', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 7', 'Test Message'), false);
        $repo->add($repo->create($user, 'Test 8', 'Test Message'), false);

        // List Notification
        $this->client($user)->request('POST', '/v1/main/notification/read-all');
        $this->isOk();

        // Empty
        $this->assertEmpty($this->manager()->getRepository(Notification::class)->findBy([
            'read' => false,
            'owner' => $user,
        ]));
    }
}
