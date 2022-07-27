<?php

namespace App\Admin\Notification\Dto;

use App\Admin\Notification\Enum\DeviceType;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class FcmRegisterDto extends AbstractApiDto
{
    #[Assert\Length(min: 5)]
    #[Assert\NotNull]
    public string $token;

    #[Assert\Choice(callback: 'getDevice')]
    #[Assert\NotNull]
    public ?string $device;

    public static function getDevice(): array
    {
        return DeviceType::values();
    }
}
