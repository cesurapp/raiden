<?php

use Package\SwooleBundle\Runtime\Runtime;

if (PHP_SAPI === 'cli') {
    $opt = getopt('', ['config::']);
    if (!empty($opt['config'])) {
        $opt = json_decode(base64_decode($opt['config']), true, 512, JSON_THROW_ON_ERROR);
    }

    $_SERVER['APP_RUNTIME'] = Runtime::class;
    $_SERVER['APP_RUNTIME_OPTIONS'] = $opt;
}
