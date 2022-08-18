<?php

namespace App\Admin\Core\Enum;

use App\Admin\Core\Permission\AdminPermissionInterface;

enum AccountPermission: string implements AdminPermissionInterface
{
    case ROLE_ACCOUNT_LIST = 'ROLE_ACCOUNT_LIST';
}
