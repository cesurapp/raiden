<?php

namespace App\Admin\Core\Entity;

use App\Admin\Core\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
class RefreshToken
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?UuidV7 $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $token;

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

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
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
}
