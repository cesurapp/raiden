<?php

namespace App\Admin\Core\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * UniqueEntity for Single|Multiple Column.
 */
class UniqueEntityConstraintValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityConstraint) {
            throw new UnexpectedTypeException($constraint, UniqueEntityConstraint::class);
        }

        $fields = !is_array($constraint->fields) ? [$constraint->fields] : $constraint->fields;
        $criteria = [];
        foreach ($fields as $columnName => $field) {
            if (!is_numeric($columnName)) {
                $criteria[$columnName] = $this->context->getObject()->{$field};
            } else {
                $criteria[$field] = $this->context->getObject()->{$field};
            }
        }

        $total = $this->entityManager->getRepository($constraint->entityClass)->count($criteria); // @phpstan-ignore-line

        if ($total > 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}
