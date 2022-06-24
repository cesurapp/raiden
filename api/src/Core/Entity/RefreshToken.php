<?php

namespace App\Core\Entity;

use App\Core\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
class RefreshToken
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiredAt;

    public function getId(): ?Ulid
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getExpiredAt(): DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}
