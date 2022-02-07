<?php

namespace Package\ApiBundle\AbstractClass;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Abstract Api Exception Class
 */
abstract class AbstractApiException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, protected array|ConstraintViolationListInterface|null $errors = null)
    {
        parent::__construct($message, $code);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}