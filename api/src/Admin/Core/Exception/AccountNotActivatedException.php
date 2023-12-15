<?php

namespace App\Admin\Core\Exception;

use Cesurapp\ApiBundle\AbstractClass\ApiException;

class AccountNotActivatedException extends ApiException
{
    public function __construct(string $message = 'Account has not been activated', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
