<?php

namespace App\Admin\Notification\Dto;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\NotificationStatus;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class NotificationDto extends AbstractApiDto
{
    #[Assert\NotNull]
    public NotificationStatus $status = NotificationStatus::INFO;

    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 1000)]
    public ?string $message = null;

    #[Assert\Type(type: ['json'])]
    public ?array $data = null;

    public function initObject(mixed $object = null): Notification
    {
        return (new Notification())
            ->setTitle($this->validated('title'))
            ->setMessage($this->validated('message') ?? '')
            ->setStatus($this->status);
    }
}
