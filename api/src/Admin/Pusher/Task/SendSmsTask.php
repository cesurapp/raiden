<?php

namespace App\Admin\Pusher\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

/**
 * Send SMS to Task.
 */
class SendSmsTask implements TaskInterface
{
    public function __construct(private TexterInterface $texter)
    {
    }

    public function __invoke(array $data = []): bool
    {
        $sms = new SmsMessage($data['phone'], $data['message']);
        if ($data['transport']) {
            $sms->transport($data['transport']);
        }

        $this->texter->send($sms);

        return true;
    }
}
