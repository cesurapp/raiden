<?php

namespace App\Admin\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Send Mail Task.
 *
 * @param array{
 *  email: string,
 * } $data
 */
class SendMailTask implements TaskInterface
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(array $data = []): bool
    {
        /** @var Email $email */
        $email = unserialize($data['email']);
        $this->mailer->send($email);

        return true;
    }
}
