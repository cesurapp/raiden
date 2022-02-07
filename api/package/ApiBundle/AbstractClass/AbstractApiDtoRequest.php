<?php

namespace Package\ApiBundle\AbstractClass;

use Package\ApiBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Data Transfer Object for Validation
 */
abstract class AbstractApiDtoRequest
{
    protected bool $auto = true;

    protected array $validationGroup = ['Default'];

    public function __construct(protected Request $request, protected ValidatorInterface $validator)
    {
        // Set Parameters
        $fields = [...$this->request->query->all(), ...$this->request->request->all(), ...$this->request->files->all()];
        foreach ($fields as $field => $value) {
            if (property_exists($this, (string)$field)) {
                $this->$field = $value;
            }
        }

        // Run Validate
        if ($this->auto) {
            $this->validate(true);
        }
    }

    /**
     * Validate DTO Request
     *
     * @throws ValidationException
     */
    final public function validate(bool $throw = false): bool
    {
        // Start Validated
        $this->beforeValidated();

        // Validate
        $constraints = $this->validator->validate($this, groups: $this->validationGroup);
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
     * Get Validated Data
     *
     * @return array
     */
    final public function validated(): array
    {
        return array_filter(get_object_vars($this), static function ($value, $key) {
            return !in_array($key, ['request', 'validator', 'auto', 'validationGroup']);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Run Before Validation
     */
    protected function beforeValidated(): void
    {

    }

    /**
     * Run Success Validation
     */
    protected function endValidated(): void
    {

    }
}