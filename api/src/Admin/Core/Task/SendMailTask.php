<?php

namespace App\Admin\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Send Mail Task.
 */
class SendMailTask implements TaskInterface
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(RawMessage|string $data): bool
    {
        $this->mailer->send($data);

        return true;
    }
}
