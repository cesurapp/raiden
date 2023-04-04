<?php

namespace App\Admin\Notification\Entity;

use App\Admin\Core\Entity\OwnerRemovalTrait;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\UniqueConstraint(fields: ['token', 'type'])]
#[ORM\HasLifecycleCallbacks]
class Device
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(type: Types::STRING, enumType: DeviceType::class)]
    private DeviceType $type = DeviceType::WEB;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getToken(): ?string
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
