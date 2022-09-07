<?php

namespace App\Admin\Core\Enum;

use App\Admin\Core\Permission\AdminPermissionInterface;

enum CorePermission: string implements AdminPermissionInterface
{
    case SWITCH_USER = 'ROLE_ALLOWED_TO_SWITCH';
}
