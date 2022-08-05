<?php

namespace App\Admin\Core\Dto;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class UserIdOtpDto extends AbstractApiDto
{
    #[Assert\NotNull]
    #[Assert\Ulid]
    public string|int $id;

    #[Assert\Length(min: 6, max: 6)]
    #[Assert\NotNull]
    public int $otp_key;
}
