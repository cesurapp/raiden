<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Validator\UsernameConstraint;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class UsernameDto extends AbstractApiDto
{
    #[UsernameConstraint]
    #[Assert\NotNull]
    public string|int $username;
}
