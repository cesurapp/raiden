<?php

namespace App\Admin\Notification\Enum;

use App\Admin\Core\Permission\Types\AdminPermissionInterface;

enum DevicePermission: string implements AdminPermissionInterface
{
    case ROLE_DEVICE_LIST = 'ROLE_DEVICE_LIST';
    case ROLE_DEVICE_DELETE = 'ROLE_DEVICE_DELETE';
    case ROLE_DEVICE_SEND = 'ROLE_DEVICE_SEND';
}
