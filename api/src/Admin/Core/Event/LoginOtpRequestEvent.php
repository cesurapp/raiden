<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Login OTP Request Event.
 */
class LoginOtpRequestEvent extends Event
{
    public const NAME = 'security.login.otp.request';

    public function __construct(private User $user, private OtpKey $otpKey)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOtpKey(): OtpKey
    {
        return $this->otpKey;
    }
}
