<?php

use Package\ApiBundle\Utils\Util;
use Package\SwooleBundle\Runtime\Runtime;
use Swoole\Constant;

if (PHP_SAPI === 'cli') {
    $opt = array_merge([
        'env' => 'dev',
        'host' => '0.0.0.0:8000',
        'worker' => 1,
        'task_worker' => 1,
        'watch' => 0,
        'cron' => 0,
    ], getopt('', ['env::', 'host::', 'worker::', 'task_worker::', 'watch::', 'cron::']));
    [$opt['host'], $opt['port']] = explode(':', $opt['host']);
    $opt['watch'] = $_SERVER['watch'] ?? $opt['watch'];

    // Set Global Options
    $_SERVER['APP_RUNTIME'] = Runtime::class;
    $_SERVER['APP_RUNTIME_OPTIONS'] = [
        'env' => $opt['env'],
        'host' =>  $opt['host'],
        'port' => (int)$opt['port'],
        'mode' => SWOOLE_PROCESS,
        'settings' => [
            Constant::OPTION_WORKER_NUM => (int)$opt['worker'],
            Constant::OPTION_TASK_WORKER_NUM => (int)$opt['task_worker'],
            Constant::OPTION_ENABLE_STATIC_HANDLER => false,
            Constant::OPTION_DOCUMENT_ROOT => Util::rootDir('public'),
            Constant::OPTION_PID_FILE => Util::rootDir("var/swoole_server.pid"),
            Constant::OPTION_LOG_FILE => Util::rootDir("var/log/{$opt['env']}_server.log"),
            Constant::OPTION_LOG_LEVEL => $opt['env'] === 'prod' ? SWOOLE_LOG_INFO : SWOOLE_LOG_DEBUG
        ],
        'watch' => (int)$opt['watch']
    ];

    if ($opt['watch']) {
        unset($_SERVER['APP_RUNTIME_OPTIONS']['settings'][Constant::OPTION_PID_FILE]);
    }
}