<?php

namespace App\Admin\Notification\Enum;

use App\Admin\Core\Permission\Types\AdminPermissionInterface;

enum SchedulerPermission: string implements AdminPermissionInterface
{
    case ROLE_SCHEDULER_LIST = 'ROLE_SCHEDULER_LIST';
    case ROLE_SCHEDULER_CREATE = 'ROLE_SCHEDULER_CREATE';
    case ROLE_SCHEDULER_EDIT = 'ROLE_SCHEDULER_EDIT';
    case ROLE_SCHEDULER_DELETE = 'ROLE_SCHEDULER_DELETE';
}
