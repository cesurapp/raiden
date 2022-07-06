<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Validator\IdentityConstraint;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ResetRequestDto extends AbstractApiDto
{
    #[IdentityConstraint]
    #[Assert\NotNull]
    public string|int $identity;
}
