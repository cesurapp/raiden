<?php

namespace App\Admin\Notification\Entity;

use App\Admin\Core\Entity\OwnerRemovalTrait;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\UniqueConstraint(fields: ['token', 'type'])]
#[ORM\HasLifecycleCallbacks]
class Device
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?UuidV7 $id;

    #[ORM\Column(length: 255)]
    private string $token;

    #[ORM\Column(type: Types::STRING, enumType: DeviceType::class)]
    private DeviceType $type = DeviceType::WEB;

    public function __construct()
    {
        $this->id = UuidV7::v7();
    }

    public function getId(): ?UuidV7
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getType(): DeviceType
    {
        return $this->type;
    }

    public function setType(DeviceType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
