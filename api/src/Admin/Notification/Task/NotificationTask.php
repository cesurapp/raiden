<?php

namespace App\Admin\Notification\Task;

use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Repository\DeviceRepository;
use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\AndroidNotification;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\IOSNotification;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\WebNotification;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;

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

    public function __invoke(array $data = []): bool
    {
        // Create Message
        $message = match ($data['device']['type']) {
            DeviceType::WEB->value => $this->sendWeb($data),
            DeviceType::IOS->value => $this->sendIos($data),
            DeviceType::ANDROID->value => $this->sendAndroid($data),
            default => false
        };

        if (!$message) {
            return false;
        }

        // Send Message
        try {
            $this->chatter->send($message);

            return true;
        } catch (TransportException $exception) {
            // Remove Device
            if (str_contains($exception->getMessage(), 'InvalidRegistration')) {
                $this->deviceRepo->removeDevice($data['device']['id']);
            }
        }

        return false;
    }

    private function sendWeb(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'],
            new WebNotification(
                $data['device']['token'],
                [
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ]
            )
        );
    }

    private function sendAndroid(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'],
            new AndroidNotification(
                $data['device']['token'],
                [
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ]
            )
        );
    }

    private function sendIos(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'],
            new IOSNotification(
                $data['device']['token'],
                [
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ]
            )
        );
    }
}
