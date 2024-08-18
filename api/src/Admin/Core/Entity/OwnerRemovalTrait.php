<?php

namespace App\Admin\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Add Owner to Entity.
 */
trait OwnerRemovalTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    private ?User $owner = null;

    public function hasOwner(): bool
    {
        return null !== $this->owner;
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
}
