<?php

use App\Kernel;
use OpenSwoole\Coroutine;

Coroutine::set(
    ['hook_flags' => OpenSwoole\Runtime::HOOK_TCP | OpenSwoole\Runtime::HOOK_PROC | OpenSwoole\Runtime::HOOK_NATIVE_CURL | OpenSwoole\Runtime::HOOK_SLEEP]
);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
require_once dirname(__DIR__).'/package/SwooleBundle/Runtime/Server.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
