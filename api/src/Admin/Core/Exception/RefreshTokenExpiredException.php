<?php

namespace App\Admin\Core\Exception;

use Cesurapp\ApiBundle\AbstractClass\ApiException;

class RefreshTokenExpiredException extends ApiException
{
    public function __construct(string $message = 'Refresh token expired!', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
