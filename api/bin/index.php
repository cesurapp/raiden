<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
require_once dirname(__DIR__).'/package/SwooleBundle/Runtime/Server.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
