<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Triggered when the user resets and changes their Password.
 */
class ResetPasswordEvent extends Event
{
    public const NAME = 'security.reset.password';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
