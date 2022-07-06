<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegisterDto extends AbstractApiDto
{
    #[Assert\Length(max: 180)]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Length(max: 25)]
    #[Assert\Type('numeric')]
    public ?string $phone = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: 'getTypes')]
    public string $type = 'user';

    #[Assert\Length(min: 8)]
    #[Assert\NotNull]
    public string $password;

    #[Assert\Length(min: 2, max: 50)]
    #[Assert\NotNull]
    public string $firstName;

    #[Assert\Length(min: 2, max: 50)]
    #[Assert\NotNull]
    public string $lastName;

    public static function getTypes(): array
    {
        return [UserType::USER->value];
    }

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
    }

    protected function beforeValidated(): void
    {
        if ($this->type) {
            $this->type = 'ROLE_'.strtoupper($this->type);
        }
    }

    /**
     * @param User $object
     */
    public function initObject(mixed $object): User
    {
        return $object
            ->setEmail($this->validated('email'))
            ->setPhone($this->validated('phone'))
            ->setType(UserType::from($this->validated('type')))
            ->setFirstName($this->validated('firstName'))
            ->setLastName($this->validated('lastName'));
    }
}
