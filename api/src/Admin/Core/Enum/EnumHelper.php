<?php

namespace App\Admin\Core\Enum;

trait EnumHelper
{
    public static function values(): array
    {
        return array_map(static fn (\BackedEnum $type) => $type->value, self::cases());
    }
}
