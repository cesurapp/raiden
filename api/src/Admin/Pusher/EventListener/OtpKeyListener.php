<?php

namespace App\Admin\Pusher\EventListener;

use App\Admin\Core\Entity\OtpKey;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Notification Created Listener.
 *
 * Send Notification to Firebase or Pusher Channel
 */
class OtpKeyListener
{
    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        // dump('OTP Key Created Listener');
    }
}
