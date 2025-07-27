<?php

namespace App\Admin\Core\Entity;

use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Repository\OtpKeyRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: OtpKeyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class OtpKey
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?UuidV7 $id;

    #[ORM\Column(type: 'integer')]
    private int $otpKey;

    #[ORM\Column(type: 'string', enumType: OtpType::class)]
    private OtpType $type;

    #[ORM\Column(type: 'string')]
    private string $address;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $phoneCountry = null;

    #[ORM\Column(type: 'boolean')]
    private bool $used = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiredAt;

    public function __construct()
    {
        $this->id = UuidV7::v7();
    }

    public function getId(): ?UuidV7
    {
        return $this->id;
    }

    public function getOtpKey(): int
    {
        return $this->otpKey;
    }

    public function setOtpKey(int $otpKey): self
    {
        $this->otpKey = $otpKey;

        return $this;
    }

    public function getType(): OtpType
    {
        return $this->type;
    }

    public function setType(OtpType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneCountry(): ?string
    {
        return $this->phoneCountry;
    }

    public function setPhoneCountry(?string $phoneCountry): self
    {
        $this->phoneCountry = $phoneCountry;

        return $this;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiredAt->getTimestamp() < time();
    }

    public function getExpiredAt(): \DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(LifecycleEventArgs $event): void
    {
        // Disable Other OTP Code
        $event->getObjectManager()->getRepository(OtpKey::class)->disableOtherCodes($this);
    }
}
