<?php

namespace Package\ApiBundle\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Resource Not Found Exception.
 */
class ResourceNotFoundException extends AbstractApiException
{
    public function __construct(
        string $message = 'Api Resource Not Found!',
        int $code = 0,
        ConstraintViolationListInterface|array|null $errors = null
    ) {
        parent::__construct($message, $code, $errors);
    }
}
