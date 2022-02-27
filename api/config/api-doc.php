<?php

/**
 * Global Api Documentation Configuration.
 */

return [
    'requireAuth' => true,
    'authHeader' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer %Token%',
    ],
    'get' => [],
    'post' => [],
    'header' => [],
    'success' => [],
    'exception' => [],
];
