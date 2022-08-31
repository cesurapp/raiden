<?php

namespace App\Admin\Notification\Task;

use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Repository\DeviceRepository;
use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\AndroidNotification;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\IOSNotification;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\WebNotification;
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

    public function __invoke(array $data = []): bool|SentMessage
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
            return $this->chatter->send($message);
        } catch (\Exception $exception) {
            if (
                str_contains($exception->getMessage(), 'InvalidRegistration') ||
                str_contains($exception->getMessage(), 'NotRegistered')
            ) {
                $this->deviceRepo->removeDevice($data['device']['id']);
            }
        }

        return false;
    }

    private function sendWeb(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'] ?? '',
            new WebNotification(
                $data['device']['token'],
                array_merge([
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ], $data['notification']['data']),
                $data['notification']
            )
        );
    }

    private function sendAndroid(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'] ?? '',
            new AndroidNotification(
                $data['device']['token'],
                array_merge([
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ], $data['notification']['data']),
                $data['notification']
            )
        );
    }

    private function sendIos(array $data = []): ChatMessage
    {
        return new ChatMessage(
            $data['notification']['title'] ?? '',
            new IOSNotification(
                $data['device']['token'],
                array_merge([
                    'title' => $data['notification']['title'],
                    'body' => $data['notification']['message'],
                ], $data['notification']['data']),
                $data['notification']
            )
        );
    }
}
