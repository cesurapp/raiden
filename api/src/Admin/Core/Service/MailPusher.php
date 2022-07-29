<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendMailTask;
use Package\SwooleBundle\Task\TaskHandler;

/**
 * Send Mail to Task Queue.
 */
class MailPusher
{
    public function __construct(private readonly TaskHandler $taskHandler)
    {
    }

    public function send(): void
    {
        $this->taskHandler->dispatch(SendMailTask::class, [
            'subject' => 'Verification Code',
            'message' => '',
        ]);
    }
}
