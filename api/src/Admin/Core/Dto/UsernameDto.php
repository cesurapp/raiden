<?php

namespace App\Admin\Core\Dto;

use Cesurapp\ApiBundle\Validator\Username;
use Cesurapp\ApiBundle\AbstractClass\ApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class UsernameDto extends ApiDto
{
    #[Username]
    #[Assert\NotNull]
    public string|int $username;
}
