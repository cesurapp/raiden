<?php

namespace App\Admin\Core\Enum;

use App\Admin\Core\Permission\PermissionInterface;

enum UserType: string implements PermissionInterface
{
    case USER = 'user';
    case ADMIN = 'admin';
    case SUPERADMIN = 'super_admin';

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }

    public static function roles(): array
    {
        return array_map(static fn (self $type) => 'ROLE_'.strtoupper($type->value), self::cases());
    }

    public function role(): string
    {
        return 'ROLE_'.strtoupper($this->value);
    }
}
