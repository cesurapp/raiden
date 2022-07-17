<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Login Event.
 */
class SecurityEvent extends Event
{
    public const LOGIN = 'security.login';
    public const REGISTER = 'security.register';
    public const RESET_REQUEST = 'security.reset.request';
    public const RESET_PASSWORD = 'security.reset.password';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
