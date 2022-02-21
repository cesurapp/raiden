<?php

namespace App\Core\Request;

use Package\ApiBundle\AbstractClass\AbstractApiDtoRequest;
use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest extends AbstractApiDtoRequest
{
    #[Assert\NotNull]
    public ?string $name;

    #[Assert\Length(min: 10, max: 100)]
    public ?string $asdsasa;

    #[Assert\Language]
    #[Assert\Length(min: 10, max: 100)]
    public ?int $phone;
}
