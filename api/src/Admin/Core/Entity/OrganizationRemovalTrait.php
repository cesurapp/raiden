<?php

namespace App\Admin\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Add Organization to Entity.
 */
trait OrganizationRemovalTrait
{
    #[ORM\ManyToOne(targetEntity: Organization::class, cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    private Organization $organization;

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}
