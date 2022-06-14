<?php

namespace App\Core\Enum;

enum UserType: string
{
    case USER = 'user';
    case SELLER = 'seller';
    case ADMIN = 'admin';
    case ROOT = 'root';
}
