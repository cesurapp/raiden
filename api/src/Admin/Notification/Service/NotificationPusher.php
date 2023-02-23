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
    private Notification $notification;

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
        string $message,
        ?string $title = null,
        NotificationType $type = NotificationType::SUCCESS,
        User $user = null
    ): self {
        $this->notification = (new Notification())
            ->setOwner($user ?? $this->currentUser->get())
            ->setType($type)
            ->setTitle($title ? $this->translator->trans($title) : null)
            ->setMessage($this->translator->trans($message));

        return $this;
    }

    public function get(): Notification
    {
        return $this->notification;
    }

    public function send(): void
    {
        $this->repo->add($this->notification);
    }
}
