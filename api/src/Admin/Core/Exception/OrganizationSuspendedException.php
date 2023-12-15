<?php

namespace App\Admin\Core\Exception;

use Cesurapp\ApiBundle\AbstractClass\ApiException;

class OrganizationSuspendedException extends ApiException
{
    public function __construct(string $message = 'The organization has been suspended', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
