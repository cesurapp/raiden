<?php

/**
 * Thor Global Api Documentation Configuration.
 */

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

return static function () {
    return [
        'requireAuth' => true,
        'authHeader' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer %Token%',
        ],
        'query' => [],
        'request' => [],
        'header' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'response' => [
            401 => BadCredentialsException::class,
        ],
        'hidden' => false,
        'paginate' => false,
    ];
};
