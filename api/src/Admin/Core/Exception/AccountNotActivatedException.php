<?php

namespace App\Admin\Core\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

class AccountNotActivatedException extends AbstractApiException
{
    public function __construct(string $message = 'Account has not been activated', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
