<?php

namespace App\Admin\Core\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

class TokenExpiredException extends AbstractApiException
{
    public function __construct(string $message = 'Token expired!', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
