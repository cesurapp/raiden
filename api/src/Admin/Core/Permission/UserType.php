<?php

namespace App\Admin\Core\Permission;

use App\Admin\Core\Enum\EnumHelper;
use App\Admin\Core\Permission\Types\PermissionInterface;

enum UserType: string implements PermissionInterface
{
    use EnumHelper;

    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case SUPERADMIN = 'ROLE_SUPER_ADMIN';

    public static function getRegisterTypes(): array
    {
        return [self::USER->value];
    }

    public static function getAdminTypes(): array
    {
        return [self::ADMIN->value, self::SUPERADMIN->value];
    }
}
