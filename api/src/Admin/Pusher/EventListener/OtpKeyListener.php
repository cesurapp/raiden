<?php

namespace App\Admin\Pusher\EventListener;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Pusher\Task\SendMailTask;
use App\Admin\Pusher\Task\SendSmsTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Package\SwooleBundle\Task\TaskHandler;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * OTP Created Listener.
 *
 * Send Email | SMS.
 */
#[Autoconfigure(tags: [[
    'name' => 'doctrine.orm.entity_listener',
    'event' => 'postPersist',
    'entity' => OtpKey::class,
    'lazy' => true,
]])]
class OtpKeyListener
{
    public function __construct(
        private readonly TaskHandler $taskHandler,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        match ($otpKey->getType()) {
            OtpType::LOGIN_EMAIL => $this->sendMail($otpKey, 'ff'),
            OtpType::REGISTER_EMAIL => $this->sendMail($otpKey, ''),
            OtpType::RESET_EMAIL => $this->sendMail($otpKey, 'aaa'),
            OtpType::LOGIN_PHONE => $this->sendPhone($otpKey, 'a'),
            OtpType::REGISTER_PHONE => $this->sendPhone($otpKey, 'c'),
            OtpType::RESET_PHONE => $this->sendPhone($otpKey, 'f'),
        };
    }

    private function sendMail(OtpKey $otpKey, string $message): void
    {
        $this->taskHandler->dispatch(SendMailTask::class, [
            'subject' => 'Verification Code',
            'message' => $this->translator->trans($message, ['%code%' => $otpKey->getOtpKey()]),
        ]);
    }

    private function sendPhone(OtpKey $otpKey, string $message): void
    {
        $this->taskHandler->dispatch(SendSmsTask::class, [
            'phone' => $otpKey->getOwner()->getPhone(),
            'country' => $otpKey->getOwner()->getPhoneCountry(),
            'title' => $this->translator->trans('Verification Code'),
            'message' => $this->translator->trans($message, ['%code%' => $otpKey->getOtpKey()]),
        ]);
    }
}
