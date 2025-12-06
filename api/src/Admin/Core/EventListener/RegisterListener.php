<?php

namespace App\Admin\Core\EventListener;

use App\Admin\Core\Event\SecurityEvent;
use App\Admin\Core\Service\MailPusher;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: SecurityEvent::REGISTER, method: 'onRegister')]
readonly class RegisterListener
{
    public function __construct(private MailPusher $mailPusher)
    {
    }

    public function onRegister(SecurityEvent $event): void
    {
        if (!$event->getUser()->getEmail()) {
            return;
        }

        $this->mailPusher->send(
            new TemplatedEmail()
                ->to($event->getUser()->getEmail())
                ->subject('Welcome to Raiden')
                ->htmlTemplate('email/welcome.html.twig')
                ->context(['user' => $event->getUser()])
        );
    }
}
