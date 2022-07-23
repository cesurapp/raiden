<?php

namespace App\Admin\Notification\Enum;

enum NotificationType: string
{
    case DANGER = 'danger';
    case WARNING = 'warning';
    case INFO = 'info';
    case SUCCESS = 'success';
}
