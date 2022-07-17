<?php

namespace App\Admin\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Add Owner to Entity.
 */
trait OwnerTrait
{
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $owner;

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
