<?php

namespace App\Admin\Core\Permission;

use App\Admin\Core\Permission\Types\AdminPermissionInterface;

enum CorePermission: string implements AdminPermissionInterface
{
    case SWITCH_USER = 'ROLE_ALLOWED_TO_SWITCH';
}
