<?php

namespace App\Core\Entity;

use App\Core\Repository\OrganizationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $frozen = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setFrozen(bool $frozen): self
    {
        $this->frozen = $frozen;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUsers(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setOrganization($this);
        }

        return $this;
    }

    public function removeUsers(User $user): self
    {
        if ($this->users->removeElement($user) && $user->getOrganization() === $this) {
            $user->setOrganization(null);
        }

        return $this;
    }
}
