<?php

namespace App\Admin\Notification\Service;

use App\Admin\Core\Service\CurrentUser;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Repository\NotificationRepository;
use App\Admin\Notification\Task\NotificationTask;
use Package\SwooleBundle\Task\TaskHandler;
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
        private CurrentUser $currentUser,
        private TaskHandler $taskHandler
    ) {
    }

    public function send(Notification $notification): void
    {
        if (!$notification->hasOwner()) {
            $notification->setOwner($this->currentUser->get());
        }

        if (!$notification->getTitle()) {
            $notification->setTitle($this->translator->trans($notification->getTitle()));
        }

        if (!$notification->getMessage()) {
            $notification->setMessage($this->translator->trans($notification->getMessage()));
        }

        $this->repo->add($notification);
    }

    /**
     * Only sended to Firebase. It is not saved in system notifications.
     */
    public function onlySend(Device $device, Notification $notification): void
    {
        $this->taskHandler->dispatch(NotificationTask::class, [
            'notification' => $notification,
            'device' => $device,
        ]);
    }
}
