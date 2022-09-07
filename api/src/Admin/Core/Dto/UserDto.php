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
    public null|int|string $phone = null;

    #[Assert\Country]
    public ?string $phone_country = null;

    #[Assert\Type(type: 'bool')]
    public bool $phone_approved = false;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [UserType::class, 'values'])]
    public ?string $type = 'user';

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
            $err = $context->getValidator()->validate($this->phone, [new NotNull(), new Assert\NotBlank()]);
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('phone')->addViolation();
            }
        } elseif (!$this->phone) {
            $err = $context->getValidator()->validate($this->email, [new NotNull(), new Assert\NotBlank()]);
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('email')->addViolation();
            }
        }

        if (!$this->id) {
            $err = $context->getValidator()->validate($this->password, new NotNull());
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('password')->addViolation();
            }
        }
    }

    protected function beforeValidated(): void
    {
        if (empty($this->password)) {
            $this->password = null;
        }
    }

    /**
     * @param User $object
     */
    public function initObject(mixed $object): User
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
