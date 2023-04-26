<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Validator\UniqueEntityConstraint;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CredentialsDto extends AbstractApiDto
{
    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['email'])]
    public ?string $email = null;

    #[PhoneNumber(regionPath: 'phone_country')]
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['phone'])]
    public int|string|null $phone = null;

    #[Assert\Country]
    public ?string $phone_country = null;

    #[Assert\Callback]
    public function callbackValidator(ExecutionContextInterface $context): void
    {
        if (!$this->email) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('phone')
                ->validate($this->phone, [new Assert\NotNull(), new Assert\NotBlank()])
                ->atPath('phone_country')
                ->validate($this->phone_country, [new Assert\NotNull()]);
        } elseif (!$this->phone) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('email')
                ->validate($this->email, [new Assert\NotNull(), new Assert\NotBlank()]);
        }
    }

    protected function beforeValidated(): void
    {
        $this->id = 'currentUser';
    }
}
