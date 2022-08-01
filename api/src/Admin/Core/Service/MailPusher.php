<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendMailTask;
use Package\SwooleBundle\Task\TaskHandler;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Send Mail to Task Queue.
 */
#[Autoconfigure(public: true)]
class MailPusher
{
    public function __construct(
        private readonly TaskHandler $taskHandler,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function send(Email $email, bool $translateSubject = true): void
    {
        // Translate
        if ($translateSubject) {
            $email->subject($this->translator->trans($email->getSubject()));
        }

        $this->taskHandler->dispatch(SendMailTask::class, [
            'email' => serialize($email),
        ]);
    }
}
