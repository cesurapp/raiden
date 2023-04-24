<?php

namespace App\Admin\Notification\Enum;

enum SchedulerStatus: string
{
    case INIT = 'init';
    case PROCESSING = 'processing';
    case ERROR = 'error';
    case SENDED = 'sended';
}
