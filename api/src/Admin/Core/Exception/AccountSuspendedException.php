<?php

namespace App\Admin\Core\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

class AccountSuspendedException extends AbstractApiException
{
    public function __construct(string $message = 'The account has been suspended', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
