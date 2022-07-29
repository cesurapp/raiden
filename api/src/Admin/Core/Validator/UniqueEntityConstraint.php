<?php

namespace App\Admin\Core\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEntityConstraint extends Constraint
{
    public string $message = 'This value is already used.';
    public string $entityClass;
    public string|array $fields;

    public function __construct(
        string $entityClass = null,
        string $message = null,
        string|array $fields = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->entityClass = $entityClass;
        $this->fields = $fields;
    }
}
