<?php

namespace App\Admin\Notification\Service;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Service\CurrentUser;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\NotificationType;
use App\Admin\Notification\Repository\NotificationRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create Notification Current User or Specific User.
 */
#[Autoconfigure(public: true)]
class NotificationPusher
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly NotificationRepository $repo,
        private readonly CurrentUser $currentUser
    ) {
    }

    /**
     * Create Notification.
     */
    public function create(
        string $title,
        string $message,
        NotificationType $type = NotificationType::INFO,
        User $user = null
    ): Notification {
        return (new Notification())
            ->setOwner($user ?? $this->currentUser->get())
            ->setType($type)
            ->setTitle($this->translator->trans($title))
            ->setMessage($this->translator->trans($message));
    }

    public function send(Notification $notification): void
    {
        $this->repo->add($notification);
    }
}
