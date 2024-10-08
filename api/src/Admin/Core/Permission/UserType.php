<?php

namespace App\Admin\Core\Permission;

use App\Admin\Core\Permission\Types\PermissionInterface;

enum UserType: string implements PermissionInterface
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case SUPERADMIN = 'ROLE_SUPER_ADMIN';

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }
}
