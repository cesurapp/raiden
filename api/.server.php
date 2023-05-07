<?php

use OpenSwoole\Constant;
use OpenSwoole\Server;

/**
 * Swoole Server Configuration.
 *
 * .server.local.php -> Uncommitted file with local overrides
 */
return static function (array $opt) {
    return [
        'entrypoint' => '/bin/index.php',
        'http' => [
            'host' => '0.0.0.0',
            'port' => 80,
            'mode' => Server::POOL_MODE,
            'sock_type' => Constant::SOCK_TCP,
            'settings' => [
                'worker_num' => 8,
                'task_worker_num' => 8,
                'enable_static_handler' => false,
                'log_level' => Constant::LOG_WARNING,
                'max_wait_time' => 60,
                'task_enable_coroutine' => true,
                'task_max_request' => 0,
                'pid_file' => $opt['project_dir'].'/var/server.pid',
                'log_file' => $opt['project_dir'].'/var/log/prod_server.log',
            ],
        ],
        'tcp' => [
            'host' => '127.0.0.1',
            'port' => 9502,
            'sock_type' => Constant::SOCK_TCP,
        ],
        'cache_table' => [
            'size' => 750,
            'column_length' => 25000,
        ],
        'cron' => [
            'interval' => 1000 * 60,
        ],
        'app' => [
            'cron' => 1,
            'task' => 1,
            'tcp' => 1,
        ],
    ];
};
