<?php

namespace App\Admin\Core\Entity;

use App\Admin\Core\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Organization
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?UuidV7 $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    private bool $frozen = false;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'organization', cascade: ['persist', 'remove'])]
    private mixed $users;

    public function __construct()
    {
        $this->id = UuidV7::v7();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?UuidV7
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
