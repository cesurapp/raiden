<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendSmsTask;
use Package\SwooleBundle\Task\TaskHandler;

/**
 * Send SMS to Task Queue.
 */
class SmsPusher
{
    public function __construct(private readonly TaskHandler $taskHandler)
    {
    }

    public function send(string|int $phone, string $countryCode, string $subject): void
    {
        $this->taskHandler->dispatch(SendSmsTask::class, [
            'phone' => $phone,
            'country' => $countryCode,
            'subject' => $subject,
        ]);
    }
}
