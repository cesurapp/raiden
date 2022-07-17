<?php

namespace App\Admin\Core\Enum;

/**
 * Security OTP Types.
 */
enum OtpType: string
{
    case LOGIN = 'login';
    case REGISTER_EMAIL = 'reg_email';
    case REGISTER_PHONE = 'reg_phone';
    case RESETTING = 'resetting';
}
