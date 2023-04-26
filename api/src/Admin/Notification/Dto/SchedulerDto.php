<?php

namespace App\Admin\Notification\Dto;

use App\Admin\Core\Enum\UserType;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Enum\NotificationStatus;
use App\Admin\Notification\Enum\SchedulerStatus;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Package\ApiBundle\Thor\Attribute\ThorResource;
use Symfony\Component\Validator\Constraints as Assert;

class SchedulerDto extends AbstractApiDto
{
    // Scheduler
    #[Assert\NotNull]
    public string $campaign_title;

    #[Assert\NotNull]
    #[Assert\Type(type: 'bool')]
    public bool $persist_notification = false;

    #[Assert\NotNull]
    #[Assert\GreaterThan(new \DateTimeImmutable())]
    public \DateTimeImmutable $send_at;

    #[Assert\NotNull]
    #[Assert\Type(type: 'bool')]
    public bool $refresh_campaign = false;

    // Notification
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

    #[Assert\Type('array')]
    #[Assert\Collection(
        fields: [
            'device.type' => new Assert\All([
                new Assert\Choice(callback: [DeviceType::class, 'values']),
            ]),
            'user.createdAt' => new Assert\Collection([
                'from' => new Assert\Optional(new Assert\DateTime(DATE_ATOM)),
                'to' => new Assert\Optional(new Assert\DateTime(DATE_ATOM)),
            ]),
            'user.type' => new Assert\All([
                new Assert\Choice(callback: [UserType::class, 'values']),
            ]),
            'user.frozen' => new Assert\Type('bool'),
            'user.language' => new Assert\Language(),
            'user.phoneCountry' => new Assert\Country(),
        ],
        allowExtraFields: false,
        allowMissingFields: true,
    )]
    #[ThorResource(data: [
        'device.type' => ['web', 'android', 'ios'],
        'user.createdAt' => [
            'from' => '?string',
            'to' => '?string',
        ],
        'user.type' => '?array',
        'user.frozen' => '?bool',
        'user.language' => '?string',
        'user.phoneCountry' => '?string',
    ])]
    public ?array $device_filter = null;

    public function initObject(Scheduler|string $object = null): Scheduler
    {
        $object
            ->setCampaignTitle($this->validated('campaign_title'))
            ->setPersistNotification($this->validated('persist_notification'))
            ->setSendAt($this->validated('send_at'))
            ->setDeviceFilter($this->validated('device_filter'))
            ->setNotification(
                (new Notification())
                    ->setTitle($this->validated('title'))
                    ->setMessage($this->validated('message') ?? '')
                    ->setStatus($this->status)
                    ->setData($this->validated('data') ?? [])
            );

        if ($this->validated('refresh_campaign')) {
            $object
                ->setStatus(SchedulerStatus::INIT)
                ->setDeliveredCount(0)
                ->setFailedCount(0);
        }

        return $object;
    }
}
