<?php

namespace App\Admin\Pusher\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Send Mail to Task.
 */
class SendMailTask implements TaskInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(array $data = []): bool
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($email);

        return true;
    }
}
