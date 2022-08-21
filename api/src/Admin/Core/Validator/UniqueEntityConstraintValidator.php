<?php

namespace App\Admin\Core\Validator;

use App\Admin\Core\Repository\BaseRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * UniqueEntity for Single|Multiple Column.
 */
class UniqueEntityConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityConstraint) {
            throw new UnexpectedTypeException($constraint, UniqueEntityConstraint::class);
        }

        if (!$value) {
            return;
        }

        $fields = !is_array($constraint->fields) ? [$constraint->fields] : $constraint->fields;
        $criteria = Criteria::create();
        foreach ($fields as $columnName => $field) {
            if (!is_numeric($columnName)) {
                $criteria->andWhere(Criteria::expr()->eq($columnName, $this->context->getObject()->{$field}));
            } else {
                $criteria->andWhere(Criteria::expr()->eq($field, $this->context->getObject()->{$field}));
            }
        }

        // Edit Mode Exclude ID
        if ($this->context->getObject()->id) {
            $criteria->andWhere(Criteria::expr()->neq('id', $this->context->getObject()->id));
        }

        /** @var BaseRepository $repo */
        $repo = $this->entityManager->getRepository($constraint->entityClass); // @phpstan-ignore-line
        $total = $repo->countBy($criteria);

        if ($total > 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}
