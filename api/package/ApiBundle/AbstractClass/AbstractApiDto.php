<?php

namespace Package\ApiBundle\AbstractClass;

use Package\ApiBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Data Transfer Object for Validation.
 */
abstract class AbstractApiDto
{
    public ?string $id = null;

    protected bool $auto = true;

    protected array $validationGroup = ['Default'];

    protected array $validated = [];

    protected ConstraintViolationListInterface $constraints;

    protected array $exclude = ['request', 'validator', 'auto', 'validationGroup', 'validated', 'exclude', 'constraints', 'id'];

    public function __construct(protected Request $request, protected ValidatorInterface $validator)
    {
        $this->constraints = new ConstraintViolationList();

        // Set Parameters
        $fields = [...$this->request->query->all(), ...$this->request->request->all(), ...$this->request->files->all()];
        foreach ($fields as $field => $value) {
            if (property_exists($this, $field)) {
                try {
                    $this->$field = is_numeric($value) ? (int) $value : $value;
                } catch (\TypeError $exception) {
                    $this->constraints->add(new ConstraintViolation(
                        'The type of this value is incorrect.',
                        'The type of this value is incorrect.',
                        [],
                        $this,
                        $field,
                        $value,
                    ));
                }
            }
        }

        // Run Validate
        if ($this->auto) {
            $this->validate(true);
        }
    }

    /**
     * Validate DTO Request.
     *
     * @throws ValidationException
     */
    final public function validate(bool $throw = false): bool
    {
        // Start Validated
        $this->beforeValidated();

        // Validate
        $constraints = $this->validator->validate($this, groups: $this->validationGroup);
        $constraints->addAll($this->constraints);
        if ($constraints->count()) {
            if (!$throw) {
                return false;
            }

            throw new ValidationException(errors: $constraints);
        }

        // End Validated
        $this->endValidated();

        return true;
    }

    /**
     * Get Validated Data.
     */
    final public function validated(?string $key = null): null|int|string|bool|array
    {
        if (!$this->validated) {
            $this->validated = array_diff_key(get_object_vars($this), array_flip($this->exclude));
        }

        return $key ? ($this->validated[$key] ?? null) : $this->validated;
    }

    /**
     * Run Before Validation.
     */
    protected function beforeValidated(): void
    {
        if ($this->request->isMethod('PUT')) {
            $this->id = $this->request->attributes->get('id', $this->request->attributes->get('uid'));
        }
    }

    /**
     * Run Success Validation.
     */
    protected function endValidated(): void
    {
    }

    /**
     * Validated Data to Object Setter.
     */
    public function initObject(mixed $object): mixed
    {
        return $object;
    }
}
