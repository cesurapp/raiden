<?php

namespace Package\ApiBundle\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Form Validation Exception
 */
class ValidationException extends AbstractApiException
{
    public function __construct($message = "Validation failed", $code = 403, protected array|ConstraintViolationListInterface|null $errors = null)
    {
        parent::__construct($message, $code, $errors);
    }

    public function getErrors(): ?array
    {
        $fields = [];

        /** @var ConstraintViolation $error */
        foreach ($this->errors as $error) {
            $fields[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $fields;
    }
}