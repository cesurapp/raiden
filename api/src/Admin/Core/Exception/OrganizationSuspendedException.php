<?php

namespace App\Admin\Core\Exception;

use Package\ApiBundle\AbstractClass\AbstractApiException;

class OrganizationSuspendedException extends AbstractApiException
{
    public function __construct(string $message = 'The organization has been suspended', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
