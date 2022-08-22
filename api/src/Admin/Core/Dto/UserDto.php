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
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['email', 'type'])]
    public ?string $email = null;

    #[Assert\Type(type: 'boolean')]
    public bool $emailApproved = false;

    #[PhoneNumber(regionPath: 'phoneCountry')]
    #[Assert\Type('numeric')]
    #[UniqueEntityConstraint(entityClass: User::class, fields: ['phone', 'type'])]
    public ?int $phone = null;

    #[Assert\Country]
    public ?string $phoneCountry = null;

    #[Assert\Type(type: 'bool')]
    public bool $phoneApproved = false;

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
    public string $firstName;

    #[Assert\Length(min: 2, max: 50)]
    #[Assert\NotNull]
    public string $lastName;

    #[Assert\Callback]
    public function callbackValidator(ExecutionContextInterface $context): void
    {
        if (!$this->email) {
            $err = $context->getValidator()->validate($this->phone, new NotNull());
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('phone')->addViolation();
            }
        }

        if (!$this->phone) {
            $err = $context->getValidator()->validate($this->email, new NotNull());
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('email')->addViolation();
            }
        }

        if ($this->id) {
            $err = $context->getValidator()->validate($this->password, new NotNull());
            if ($err->count() > 0) {
                $context->buildViolation($err->get(0)->getMessage())->atPath('password')->addViolation();
            }
        }
    }

    /**
     * @param User $object
     */
    public function initObject(mixed $object): User
    {
        return $object
            ->setEmail($this->validated('email'))
            ->setEmailApproved($this->validated('emailApproved'))
            ->setPhone($this->validated('phone'))
            ->setPhoneCountry($this->validated('phoneCountry'))
            ->setPhoneApproved($this->validated('phoneApproved'))
            ->setType(UserType::from($this->validated('type')))
            ->setFrozen($this->validated('frozen'))
            ->setFirstName($this->validated('firstName'))
            ->setLastName($this->validated('lastName'));
    }
}
