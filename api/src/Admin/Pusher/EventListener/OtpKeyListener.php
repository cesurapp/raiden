<?php

namespace App\Admin\Pusher\EventListener;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Pusher\Task\SendMailTask;
use App\Admin\Pusher\Task\SendSmsTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Package\SwooleBundle\Task\TaskHandler;

/**
 * OTP Created Listener.
 *
 * Send Email | SMS.
 */
class OtpKeyListener
{
    public function __construct(private TaskHandler $taskHandler)
    {
    }

    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        // Send Register Email Code
        if (OtpType::REGISTER_EMAIL === $otpKey->getType()) {
            $this->taskHandler->dispatch(SendMailTask::class, [
                'asd' => 'asdsad',
            ]);
            return;
        }

        // Send Register Phone Code
        if (OtpType::REGISTER_PHONE === $otpKey->getType()) {
            $this->taskHandler->dispatch(SendSmsTask::class, [
                'phone' => $otpKey->getOwner()->getPhone(),
                'phoneCountry' => $otpKey->getOwner()->getPhoneCountry(),
                'title' => 'Verification Code',
                'message' => '',
            ]);
            return;
        }

       /* if (in_array($otpKey->getType(), [OtpType::LOGIN, OtpType::RESETTING], true)) {

        }*/
    }
}
