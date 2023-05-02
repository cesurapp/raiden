<?php

namespace App\Admin\Notification\Dto;

use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\NotificationStatus;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Package\ApiBundle\Thor\Attribute\ThorResource;
use Symfony\Component\Validator\Constraints as Assert;

class NotificationDto extends AbstractApiDto
{
    #[Assert\NotNull]
    public NotificationStatus $status = NotificationStatus::INFO;

    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 1000)]
    public ?string $message = null;

    #[Assert\Type('array')]
    #[Assert\Collection(
        fields: [
            'web' => new Assert\Collection([
                'icon' => new Assert\Optional(),
                'sound' => new Assert\Optional(),
                'color' => new Assert\Optional(),
                'click_action' => new Assert\Optional(),
                'route_action' => new Assert\Optional(),
                'download_action' => new Assert\Optional(),
            ]),
            'ios' => new Assert\Collection([
                'icon' => new Assert\Optional(),
                'sound' => new Assert\Optional(),
                'color' => new Assert\Optional(),
                'click_action' => new Assert\Optional(),
                'route_action' => new Assert\Optional(),
                'download_action' => new Assert\Optional(),
            ]),
            'android' => new Assert\Collection([
                'icon' => new Assert\Optional(),
                'sound' => new Assert\Optional(),
                'color' => new Assert\Optional(),
                'click_action' => new Assert\Optional(),
                'route_action' => new Assert\Optional(),
                'download_action' => new Assert\Optional(),
            ]),
        ],
        allowMissingFields: true
    )]
    #[ThorResource(data: [
        'web' => [
            'icon' => '?string',
            'sound' => '?string',
            'color' => '?string',
            'click_action' => '?string',
            'route_action' => '?string',
            'download_action' => '?string',
        ],
        'ios' => [
            'icon' => '?string',
            'sound' => '?string',
            'color' => '?string',
            'click_action' => '?string',
            'route_action' => '?string',
            'download_action' => '?string',
        ],
        'android' => [
            'icon' => '?string',
            'sound' => '?string',
            'color' => '?string',
            'click_action' => '?string',
            'route_action' => '?string',
            'download_action' => '?string',
        ],
    ])]
    public ?array $data = null;

    public function initObject(Notification|string $object = null): Notification
    {
        return (new Notification())
            ->setTitle($this->validated('title'))
            ->setMessage($this->validated('message') ?? '')
            ->setStatus($this->status)
            ->setData($this->validated('data') ?? []);
    }
}
