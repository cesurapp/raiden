<?php

namespace App\Admin\Core\Permission;

use App\Admin\Core\Permission\Types\AdminPermissionInterface;

enum AccountPermission: string implements AdminPermissionInterface
{
    case ROLE_ACCOUNT_LIST = 'ROLE_ACCOUNT_LIST';
    case ROLE_ACCOUNT_CREATE = 'ROLE_ACCOUNT_CREATE';
    case ROLE_ACCOUNT_EDIT = 'ROLE_ACCOUNT_EDIT';
    case ROLE_ACCOUNT_DELETE = 'ROLE_ACCOUNT_DELETE';
    case ROLE_ACCOUNT_PERMISSION = 'ROLE_ACCOUNT_PERMISSION';
}
