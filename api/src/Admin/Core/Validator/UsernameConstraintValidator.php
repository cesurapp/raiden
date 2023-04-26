<?php

namespace App\Admin\Core\Validator;

use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Identity Validator (Email-Phone).
 */
class UsernameConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly PhoneNumberUtil $util)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UsernameConstraint) {
            throw new UnexpectedTypeException($constraint, UsernameConstraint::class);
        }

        // Check Number
        if (is_numeric($value)) {
            if (strlen($value) < 8) {
                $this->context->addViolation('Please enter a valid phone number');

                return;
            }

            // Parse Region
            $parse = $this->util->parse('+'.$value);
            if (!$parse->getCountryCode()) {
                $this->context->addViolation('Please enter a valid phone number');

                return;
            }

            $region = $this->util->getRegionCodeForCountryCode($parse->getCountryCode());
            $this->context->getValidator()
                ->inContext($this->context)
                ->validate($value, new PhoneNumber(defaultRegion: $region));

            return;
        }

        // Check Email
        $errors = $this->context->getValidator()->validate($value, new Email());
        if ($errors->count() > 0) {
            $this->context->addViolation($errors->get(0)->getMessage());
        }
    }
}
