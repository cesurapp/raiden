<?php

namespace App\Admin\Notification\Entity;

use App\Admin\Core\Entity\OwnerRemovalTrait;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Enum\NotificationStatus;
use App\Admin\Notification\Repository\NotificationRepository;
use App\Admin\Notification\Resource\NotificationResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\WebNotification;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::STRING, enumType: NotificationStatus::class)]
    private NotificationStatus $status = NotificationStatus::INFO;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $readed = false;

    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getStatus(): NotificationStatus
    {
        return $this->status;
    }

    public function setStatus(NotificationStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function isReaded(): ?bool
    {
        return $this->readed;
    }

    public function setReaded(bool $readed): self
    {
        $this->readed = $readed;

        return $this;
    }

    /**
     * Custom FCM Data.
     */
    public function getData(?DeviceType $type = null): array
    {
        return $type ? ($this->data[$type->value] ?? []) : $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addData(string $key, string|int|bool $value, ?DeviceType $type = null): self
    {
        if (!$type) {
            foreach (DeviceType::cases() as $deviceType) {
                $this->data[$deviceType->value][$key] = $value;
            }
        } else {
            $this->data[$type->value][$key] = $value;
        }

        return $this;
    }

    public function addIcon(string $icon, ?DeviceType $type = null): self
    {
        $this->addData('icon', $icon, $type);

        return $this;
    }

    public function addSound(string $sound, ?DeviceType $type = null): self
    {
        $this->addData('sound', $sound, $type);

        return $this;
    }

    public function addColor(string $rgbColor, ?DeviceType $type = null): self
    {
        $this->addData('color', $rgbColor, $type);

        return $this;
    }

    public function addClickAction(string $link, ?DeviceType $type = null): self
    {
        $this->addData('click_action', $link, $type);

        return $this;
    }

    public function addRouteAction(string $link, ?DeviceType $type = null): self
    {
        $this->addData('route_action', $link, $type);

        return $this;
    }

    public function addDownloadAction(string $link, ?DeviceType $type = null): self
    {
        $this->addData('download_action', $link, $type);

        return $this;
    }

    /**
     * Firebase Notification Config.
     */
    public function getFCMOptions(Device $device): WebNotification
    {
        return new WebNotification(
            $device->getToken(),
            ['title' => $this->getTitle() ?? '', 'body' => $this->getMessage() ?? ''],
            ['item' => json_encode((new NotificationResource())->toArray($this, $device->getType()->value), JSON_THROW_ON_ERROR)]
        );
    }
}
