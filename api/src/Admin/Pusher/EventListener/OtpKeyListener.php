<?php

namespace App\Admin\Pusher\EventListener;

use App\Admin\Core\Entity\OtpKey;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * OTP Created Listener.
 *
 * Send Email | SMS.
 */
class OtpKeyListener
{
    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        // dump('OTP Key Created Listener');
    }
}
