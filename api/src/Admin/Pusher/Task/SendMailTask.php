<?php

namespace App\Admin\Pusher\Task;

use Package\SwooleBundle\Task\TaskInterface;

class SendMailTask implements TaskInterface
{
    public function __invoke(array $data = []): bool
    {
        return true;
    }
}