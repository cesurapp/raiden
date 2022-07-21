<?php

namespace App\Admin\Notification\EventListener;

use App\Admin\Core\Entity\OtpKey;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * OTP Created Listener.
 *
 * Send Email | SMS.
 */
class OtpListener
{
    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        dump('OTP Key Created Listener');
    }
}
