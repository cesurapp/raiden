<?php

namespace App\Admin\Core\Enum;

/**
 * Security OTP Types.
 */
enum OtpType: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';
}
