<?php

namespace App\Admin\Core\Enum;

/**
 * Security OTP Types.
 */
enum OtpType: string
{
    case LOGIN_EMAIL = 'log_email';
    case LOGIN_PHONE = 'log_phone';
    case REGISTER_EMAIL = 'reg_email';
    case REGISTER_PHONE = 'reg_phone';
    case RESET_EMAIL = 'reset_email';
    case RESET_PHONE = 'reset_phone';
}
