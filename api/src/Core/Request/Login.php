<?php

namespace App\Core\Request;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class Login extends AbstractApiDto
{
    #[Assert\NotNull]
    public string|int|null|bool $name;

    #[Assert\Length(min: 10, max: 100)]
    public ?string $lastName;

    #[Assert\Language]
    #[Assert\Length(min: 10, max: 100)]
    public ?int $phone;

    #[Assert\Optional([
        new Assert\Type('array'),
        new Assert\Count(['min' => 1]),
        new Assert\All([
            new Assert\Collection([
                'slug' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ],
                'label' => [
                    new Assert\NotBlank(),
                ],
            ]),
        ]),
    ])]
    public ?array $data;
}
