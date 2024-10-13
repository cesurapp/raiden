<?php

namespace App\Admin\Core\Enum;

/**
 * Security OTP Types.
 */
enum OtpType: string
{
    case AUTH = 'auth';
    case CREDENTIALS = 'credentials';
}
