<?php

namespace App\Admin\Core\Exception;

use Cesurapp\ApiBundle\AbstractClass\ApiException;

class AccountSuspendedException extends ApiException
{
    public function __construct(string $message = 'The account has been suspended', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
