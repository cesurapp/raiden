<?php

namespace App\Admin\Core\Dto;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordDto extends AbstractApiDto
{
    #[Assert\Length(min: 8, max: 50)]
    #[Assert\NotNull]
    public string $password;

    #[Assert\Length(min: 8, max: 50)]
    #[Assert\EqualTo(propertyPath: 'password')]
    #[Assert\NotNull]
    public string $password_confirm;
}
