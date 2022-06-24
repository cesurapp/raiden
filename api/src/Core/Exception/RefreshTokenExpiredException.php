<?php

namespace App\Core\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

class RefreshTokenExpiredException extends AbstractApiException
{
    public function __construct(string $message = 'Refresh token expired!', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}