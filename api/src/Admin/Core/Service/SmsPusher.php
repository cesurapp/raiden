<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendSmsTask;
use Package\SwooleBundle\Task\TaskHandler;

/**
 * Send SMS to Task Queue.
 */
class SmsPusher
{
    private string|int $phone;
    private string $countryCode;
    private string $subject;

    public function __construct(private readonly TaskHandler $taskHandler)
    {
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function setPhone(int|string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function send(): void
    {
        $this->taskHandler->dispatch(SendSmsTask::class, [
            'phone' => $this->phone,
            'country' => $this->countryCode,
            'subject' => $this->subject,
        ]);
    }
}
