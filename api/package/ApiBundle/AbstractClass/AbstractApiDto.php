<?php

namespace Package\ApiBundle\AbstractClass;

use Monolog\DateTimeImmutable;
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
    protected ?string $id = null;

    protected bool $auto = true;

    protected array $validationGroup = ['Default'];

    protected array $validated = [];

    protected ConstraintViolationListInterface $constraints;

    protected array $exclude = [
        'request',
        'validator',
        'auto',
        'validationGroup',
        'validated',
        'exclude',
        'constraints',
        'id',
    ];

    public function __construct(protected Request $request, protected ValidatorInterface $validator)
    {
        $this->constraints = new ConstraintViolationList();

        // Set Parameters
        $fields = [...$this->request->query->all(), ...$this->request->request->all(), ...$this->request->files->all()];
        $this->initProperties($fields);

        // Run Validate
        if ($this->auto) {
            $this->validate(true);
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Validate DTO Request.
     *
     * @throws ValidationException
     */
    final public function validate(bool $throw = false): bool
    {
        // Append ID for Edit Request
        if ($this->request->isMethod('PUT')) {
            $this->id = $this->request->attributes->get('id', $this->request->attributes->get('uid'));
        }

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
    final public function validated(?string $key = null): mixed
    {
        if (!$this->validated) {
            $this->validated = array_diff_key(get_object_vars($this), array_flip($this->exclude));
        }

        if ($key) {
            if (!isset($this->validated[$key])) {
                return null;
            }

            if ('' === $this->validated[$key]) {
                return null;
            }

            return $this->validated[$key];
        }

        return $this->validated;
    }

    /**
     * Run Before Validation.
     */
    protected function beforeValidated(): void
    {
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
    public function initObject(string $object): mixed
    {
        return $object;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    private function initProperties(array $fields): void
    {
        $refClass = new \ReflectionClass(static::class);

        foreach ($fields as $field => $value) {
            if ($refClass->hasProperty($field)) {
                $propType = $refClass->getProperty($field)->getType();
                $name = $propType instanceof \ReflectionUnionType ?
                    $propType->getTypes()[0]->getName() :
                    $propType->getName(); // @phpstan-ignore-line

                try {
                    $data = match ($name) {
                        'DateTime' => \DateTime::createFromFormat('d/m/Y H:i', $value),
                        'DateTimeImmutable' => DateTimeImmutable::createFromFormat('d/m/Y H:i', $value),
                        'bool' => (bool) $value,
                        'int' => (int) $value,
                        'string' => (string) $value,
                        default => $value
                    };

                    if (enum_exists($name)) {
                        $this->$field = $name::from($value);
                    } else {
                        $this->$field = $data;
                    }
                } catch (\Throwable) {
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
    }
}
