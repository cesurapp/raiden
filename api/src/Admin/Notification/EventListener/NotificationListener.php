<?php

namespace App\Admin\Notification\EventListener;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Task\NotificationTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Package\SwooleBundle\Task\TaskHandler;

/**
 * Notification Created Listener.
 *
 * Send Notification to Firebase Channel
 */
class NotificationListener
{
    public function __construct(
        private readonly DeviceRepository $deviceRepo,
        private readonly TaskHandler $taskHandler
    ) {
    }

    public function postPersist(Notification $notification, LifecycleEventArgs $event): void
    {
        $devices = $this->deviceRepo->getDevices($notification->getOwner());
        if ($devices) {
            foreach ($devices as $device) {
                $this->taskHandler->dispatch(NotificationTask::class, [
                    'notification' => $notification->jsonSerialize(),
                    'device' => $device->jsonSerialize(),
                ]);
            }
        }
    }
}
