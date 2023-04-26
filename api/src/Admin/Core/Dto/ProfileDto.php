<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProfileDto extends AbstractApiDto
{
    #[Assert\Length(min: 8)]
    public ?string $password = null;

    public ?string $current_password = null;

    #[Assert\Language]
    public ?string $language;

    #[Assert\Length(min: 2, max: 50)]
    #[Assert\NotNull]
    public string $first_name;

    #[Assert\Length(min: 2, max: 50)]
    #[Assert\NotNull]
    public string $last_name;

    #[Assert\Callback]
    public function callbackValidator(ExecutionContextInterface $context): void
    {
        if ($this->password || $this->current_password) {
            $err = $context->getValidator()->validate($this->current_password, new SecurityAssert\UserPassword());
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('current_password')->addViolation();
            }
        }
    }

    protected function beforeValidated(): void
    {
        if (empty($this->password)) {
            $this->password = null;
        }
    }

    public function initObject(string|User $object): User
    {
        return $object
            ->setLanguage($this->validated('language'))
            ->setFirstName($this->validated('first_name'))
            ->setLastName($this->validated('last_name'));
    }
}
