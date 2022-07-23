<?php

namespace App\Admin\Pusher\EventListener;

use App\Admin\Notification\Entity\Notification;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Notification Created Listener.
 *
 * Send Notification to Firebase or Pusher Channel
 */
class NotificationListener
{
    public function postPersist(Notification $notification, LifecycleEventArgs $event): void
    {
        // dump('OTP Key Created Listener');
    }
}
