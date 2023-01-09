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
readonly class NotificationPusher
{
    public function __construct(
        private TranslatorInterface $translator,
        private NotificationRepository $repo,
        private CurrentUser $currentUser
    ) {
    }

    /**
     * Create Notification.
     */
    public function create(
        string $message,
        ?string $title = null,
        NotificationType $type = NotificationType::SUCCESS,
        User $user = null
    ): Notification {
        return (new Notification())
            ->setOwner($user ?? $this->currentUser->get())
            ->setType($type)
            ->setTitle($title ? $this->translator->trans($title) : null)
            ->setMessage($this->translator->trans($message));
    }

    public function send(Notification $notification): void
    {
        $this->repo->add($notification);
    }
}
