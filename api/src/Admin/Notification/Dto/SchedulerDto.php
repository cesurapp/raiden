<?php

namespace App\Admin\Notification\Dto;

use App\Admin\Core\Permission\UserType;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Enum\SchedulerStatus;
use Cesurapp\ApiBundle\Thor\Attribute\ThorResource;
use Symfony\Component\Validator\Constraints as Assert;

class SchedulerDto extends NotificationDto
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

    public function initObject(mixed $object = null): mixed
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
