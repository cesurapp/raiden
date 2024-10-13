<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use Cesurapp\ApiBundle\AbstractClass\ApiDto;
use Cesurapp\ApiBundle\Validator\PhoneNumber;
use Cesurapp\ApiBundle\Validator\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserDto extends ApiDto
{
    protected bool $auto = false;

    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[UniqueEntity(entityClass: User::class, fields: ['email'], editField: 'id')]
    public ?string $email = null;

    #[Assert\Type(type: 'boolean')]
    public bool $email_approved = false;

    #[PhoneNumber(regionPath: 'phone_country')]
    #[UniqueEntity(entityClass: User::class, fields: ['phone'], editField: 'id')]
    public int|string|null $phone = null;

    #[Assert\Country]
    public ?string $phone_country = null;

    #[Assert\Type(type: 'bool')]
    public bool $phone_approved = false;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [UserType::class, 'values'])]
    public ?string $type = UserType::USER->value;

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
                ->validate($this->email, [new Assert\NotNull(), new Assert\NotBlank()]);
        }

        if (empty($this->id)) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('password')
                ->validate($this->password, new Assert\NotNull());
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
    public function initObject(mixed $object = null): User
    {
        return $object
            ->setEmail($this->validated('email') ? strtolower($this->validated('email')) : null)
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
