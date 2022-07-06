<?php

namespace App\Admin\Core\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Identity Validator (Email-Phone).
 */
class IdentityConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IdentityConstraint) {
            throw new UnexpectedTypeException($constraint, IdentityConstraint::class);
        }

        // Check Number
        if (is_numeric($value)) {
            if (strlen($value) < 9) {
                $this->context->addViolation('Please enter a valid phone number.');
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
