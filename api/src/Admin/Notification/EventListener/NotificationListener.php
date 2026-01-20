<?php

namespace App\Admin\Notification\EventListener;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Task\NotificationTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Cesurapp\SwooleBundle\Task\TaskHandler;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Notification Created Listener.
 *
 * Send Notification to Firebase Channel
 */
#[Autoconfigure(tags: [
    [
        'name' => 'doctrine.orm.entity_listener',
        'event' => 'postPersist',
        'entity' => Notification::class,
        'lazy' => true,
    ],
])]
readonly class NotificationListener
{
    public function __construct(private DeviceRepository $deviceRepo, private TaskHandler $taskHandler)
    {
    }

    public function postPersist(Notification $notification, LifecycleEventArgs $event): void
    {
        if (str_contains(getenv('FIREBASE_DSN'), 'USER:PASS')) {
            return;
        }

        $devices = $this->deviceRepo->getDevices($notification->getOwner());
        if ($devices) {
            foreach ($devices as $device) {
                $this->taskHandler->dispatch(NotificationTask::class, [
                    'notification' => $notification,
                    'device' => $device,
                ]);
            }
        }
    }
}
