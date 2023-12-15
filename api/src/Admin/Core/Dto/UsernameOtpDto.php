<?php

namespace App\Admin\Core\Dto;

use Cesurapp\ApiBundle\Validator\Username;
use Cesurapp\ApiBundle\AbstractClass\ApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class UsernameOtpDto extends ApiDto
{
    #[Username]
    #[Assert\NotNull]
    public string|int $username;

    #[Assert\Length(min: 6, max: 6)]
    #[Assert\NotNull]
    public int $otp_key;
}
