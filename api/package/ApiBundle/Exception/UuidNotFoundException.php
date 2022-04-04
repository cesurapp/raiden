<?php

namespace Package\ApiBundle\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

/**
 * Resource Not Found Exception.
 */
class UuidNotFoundException extends AbstractApiException
{
    public function __construct(string $message = 'Uuid Not Found!', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
