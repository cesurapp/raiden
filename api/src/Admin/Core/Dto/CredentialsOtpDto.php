<?php

namespace App\Admin\Core\Dto;

use App\Admin\Core\Entity\User;
use Cesurapp\ApiBundle\Validator\PhoneNumber;
use Cesurapp\ApiBundle\Validator\UniqueEntity;
use Cesurapp\ApiBundle\AbstractClass\ApiDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CredentialsOtpDto extends ApiDto
{
    protected bool $auto = false;

    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[UniqueEntity(entityClass: User::class, fields: ['email'], editField: 'id')]
    public ?string $email = null;

    #[PhoneNumber(regionPath: 'phone_country')]
    #[UniqueEntity(entityClass: User::class, fields: ['phone'], editField: 'id')]
    public int|string|null $phone = null;

    #[Assert\Country]
    public ?string $phone_country = null;

    #[Assert\Length(min: 6, max: 6)]
    #[Assert\NotNull]
    public int $otp_key;

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
}
