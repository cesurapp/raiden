<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Triggered when user registration is complete.
 */
class RegisterEvent extends Event
{
    public const NAME = 'security.register';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
