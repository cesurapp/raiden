<?php

namespace Package\ApiBundle\AbstractClass;

/**
 * Abstract Api Exception Class.
 */
abstract class AbstractApiException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, protected array|null $errors = null)
    {
        parent::__construct($message, $code);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
