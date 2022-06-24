<?php

/**
 * Thor Global Api Documentation Configuration.
 */

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
        'response' => [],
        'hidden' => false,
        'paginate' => false,
    ];
};
