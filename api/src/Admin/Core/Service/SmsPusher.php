<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendSmsTask;
use Cesurapp\SwooleBundle\Task\TaskHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Send SMS to Task Queue.
 */
readonly class SmsPusher
{
    public function __construct(
        private TaskHandler $taskHandler,
        private TranslatorInterface $translator,
    ) {
    }

    public function send(int|string $phone, string $countryCode, string $subject, bool $translateSubject = true): void
    {
        // Translate Subject
        if ($translateSubject) {
            $subject = $this->translator->trans($subject);
        }

        $this->taskHandler->dispatch(SendSmsTask::class, [
            'subject' => $subject,
            'phone' => $phone,
            'countryCode' => $countryCode,
        ]);
    }
}
