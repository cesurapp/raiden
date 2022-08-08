<?php

namespace App\Admin\Core\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Identity Validator (Email-Phone).
 */
class UsernameConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UsernameConstraint) {
            throw new UnexpectedTypeException($constraint, UsernameConstraint::class);
        }

        // Check Number
        if (is_numeric($value)) {
            if (strlen($value) < 8) {
                $this->context->addViolation('Please enter a valid phone number');
            }

            return;
        }

        // Check Email
        $errors = $this->context->getValidator()->validate($value, new Email());
        if ($errors->count() > 0) {
            $this->context->addViolation($errors->get(0)->getMessage());
        }
    }
}
