<?php

use Swoole\Constant;

/*
 * Swoole Server Configuration
 *
 * .server.local.php -> uncommitted file with local overrides
 */
return static function () {
    return [
        'entrypoint' => '/bin/index.php',
        'http' => [
            'host' => '0.0.0.0',
            'port' => 80,
            'mode' => 2,
            'sock_type' => SWOOLE_SOCK_TCP,
            'settings' => [
                Constant::OPTION_WORKER_NUM => 8,
                Constant::OPTION_TASK_WORKER_NUM => 4,
                Constant::OPTION_ENABLE_STATIC_HANDLER => false,
                Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_WARNING,
                Constant::OPTION_MAX_WAIT_TIME => 60,
            ],
        ],
        'tcp' => [
            'host' => '127.0.0.1',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
        ],
        'cache_table' => [
            'size' => 500,
            'column_length' => 3000,
        ],
        'cron' => [
            'interval' => 1000 * 60,
        ],
        'app' => [
            'cron' => 0,
            'task' => 1,
            'tcp' => 1,
        ],
    ];
};
