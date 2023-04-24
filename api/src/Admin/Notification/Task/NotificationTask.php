<?php

namespace App\Admin\Notification\Task;

use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Repository\DeviceRepository;
use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SentMessage;

/**
 * Send Notification to FCM Device Task.
 */
class NotificationTask implements TaskInterface
{
    public function __construct(
        private readonly ChatterInterface $chatter,
        private readonly DeviceRepository $deviceRepo
    ) {
    }

    public function __invoke(array|string $data): bool|SentMessage
    {
        /** @var Notification $notification */
        $notification = $data['notification'];

        /** @var Device $device */
        $device = $data['device'];

        try {
            // Send
            $result = $this->chatter->send(
                new ChatMessage($notification->getTitle() ?? '', $notification->getFCMOptions($device))
            );

            // Successful Send
            if ($notification->getId()) {
                $this->deviceRepo->em()->getRepository(Notification::class)->setForwarded($notification);
            }

            return $result;
        } catch (\Throwable $exception) {
            if (preg_match('/(InvalidRegistration|NotRegistered)/i', $exception->getMessage())) {
                if ($device->getId()) {
                    $this->deviceRepo->removeDevice($device->getId());
                }
            } else {
                throw $exception;
            }
        }

        return false;
    }
}
