<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendSmsTask;
use Cesurapp\SwooleBundle\Task\TaskHandler;

/**
 * Send SMS to Task Queue.
 */
readonly class SmsPusher
{
    public function __construct(private TaskHandler $taskHandler)
    {
    }

    public function send(int|string $phone, string $countryCode, string $subject): void
    {
        $this->taskHandler->dispatch(SendSmsTask::class, [
            'subject' => $subject,
            'phone' => $phone,
            'countryCode' => $countryCode,
        ]);
    }
}
