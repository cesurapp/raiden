<?php

namespace App\Admin\Core\EventListener;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Service\MailPusher;
use App\Admin\Core\Service\SmsPusher;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * OTP Created Listener.
 *
 * Send Email | SMS.
 */
#[AsEntityListener(Events::postPersist, method: 'postPersist', lazy: true, entity: OtpKey::class)]
readonly class OtpKeyListener
{
    public function __construct(
        private SmsPusher $smsPusher,
        private MailPusher $mailPusher,
        private TranslatorInterface $translator,
    ) {
    }

    public function postPersist(OtpKey $otpKey, LifecycleEventArgs $event): void
    {
        match (is_numeric($otpKey->getAddress())) {
            true => $this->sendPhone($otpKey),
            false => $this->sendMail($otpKey),
        };
    }

    private function sendMail(OtpKey $otpKey): void
    {
        $this->mailPusher->send(
            (new TemplatedEmail())
                ->to($otpKey->getAddress())
                ->subject('Verification Code')
                ->htmlTemplate('email/otpcode.html.twig')
                ->context([
                    'code' => $otpKey->getOtpKey(),
                    'user' => $otpKey->getOwner(),
                ])
        );
    }

    private function sendPhone(OtpKey $otpKey): void
    {
        $this->smsPusher->send(
            $otpKey->getAddress(),
            $otpKey->getPhoneCountry(),
            $this->translator->trans('Verification code: %otpkey%', ['%otpkey%' => $otpKey->getOtpKey()]),
            false
        );
    }
}
