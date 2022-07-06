<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Login Event.
 */
class LoginEvent extends Event
{
    public const NAME = 'security.login';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
