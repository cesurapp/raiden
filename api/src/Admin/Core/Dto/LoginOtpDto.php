<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Validator\UsernameConstraint;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class LoginOtpDto extends AbstractApiDto
{
    #[UsernameConstraint]
    #[Assert\NotNull]
    public string|int $username;

    #[Assert\Length(min: 6, max: 6)]
    #[Assert\NotNull]
    public string $otp_key;
}
