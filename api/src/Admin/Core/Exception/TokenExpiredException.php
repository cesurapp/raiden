<?php

namespace App\Admin\Core\Exception;

use Cesurapp\ApiBundle\AbstractClass\ApiException;

class TokenExpiredException extends ApiException
{
    public function __construct(string $message = 'Token expired!', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
