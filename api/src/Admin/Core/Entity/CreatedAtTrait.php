<?php

namespace App\Admin\Core\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Add CreatedAt.
 *
 * Entity Append
 * #[ORM\HasLifecycleCallbacks]
 */
trait CreatedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersistCreatedAt(LifecycleEventArgs $event): void
    {
        if (! $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}
