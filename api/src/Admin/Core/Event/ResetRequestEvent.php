<?php

namespace App\Admin\Core\Event;

use App\Admin\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Triggered on user password reset request.
 */
class ResetRequestEvent extends Event
{
    public const NAME = 'security.reset.request';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
