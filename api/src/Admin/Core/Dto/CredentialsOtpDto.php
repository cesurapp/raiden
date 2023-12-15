<?php

namespace App\Admin\Core\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CredentialsOtpDto extends CredentialsDto
{
    #[Assert\Length(min: 6, max: 6)]
    #[Assert\NotNull]
    public int $otp_key;
}
