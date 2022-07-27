<?php

namespace App\Admin\Notification\Enum;

enum DeviceType: string
{
    case WEB = 'web';
    case ANDROID = 'android';
    case IOS = 'ios';
    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }
}
