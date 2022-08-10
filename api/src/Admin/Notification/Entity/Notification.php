<?php

namespace App\Admin\Notification\Entity;

use App\Admin\Core\Entity\OwnerRemovalTrait;
use App\Admin\Notification\Enum\NotificationType;
use App\Admin\Notification\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification implements \JsonSerializable
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::STRING, enumType: NotificationType::class)]
    private NotificationType $type = NotificationType::INFO;

    #[ORM\Column(type: Types::STRING, length: 255)]
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

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function setType(NotificationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
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

    public function getData(): array
    {
        return $this->data;
    }

    public function addData(string $key, string|int|bool $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function removeData(string $key): self
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addClickAction(string $link): self
    {
        $this->data['click_action'] = $link;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toBase32(),
            'type' => $this->type->value,
            'title' => $this->title,
            'message' => $this->message,
            'readed' => $this->readed,
            'data' => $this->data,
            'createdAt' => $this->getId()->getDateTime(),
        ];
    }
}
