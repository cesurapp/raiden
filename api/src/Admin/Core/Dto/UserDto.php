<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Validator\UniqueEntityConstraint;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserDto extends AbstractApiDto
{
    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['email'])]
    public ?string $email = null;

    #[Assert\Type(type: 'boolean')]
    public bool $email_approved = false;

    #[PhoneNumber(regionPath: 'phone_country')]
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['phone'])]
    public int|string|null $phone = null;

    #[Assert\Country]
    public ?string $phone_country = null;

    #[Assert\Type(type: 'bool')]
    public bool $phone_approved = false;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [UserType::class, 'values'])]
    public ?string $type = 'ROLE_USER';

    #[Assert\Length(min: 8)]
    public ?string $password = null;

    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    public bool $frozen;

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
                ->validate($this->email, [new NotNull(), new Assert\NotBlank()]);
        }

        if (!$this->id) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('password')
                ->validate($this->password, new NotNull());
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
            ->setEmail($this->validated('email'))
            ->setEmailApproved($this->validated('email_approved'))
            ->setPhone($this->validated('phone'))
            ->setPhoneCountry($this->validated('phone_country'))
            ->setPhoneApproved($this->validated('phone_approved'))
            ->setLanguage($this->validated('language'))
            ->setType(UserType::from($this->validated('type')))
            ->setFrozen($this->validated('frozen'))
            ->setFirstName($this->validated('first_name'))
            ->setLastName($this->validated('last_name'));
    }
}
