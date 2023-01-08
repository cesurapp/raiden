<?php

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
            'mode' => SWOOLE_PROCESS,
            'sock_type' => SWOOLE_SOCK_TCP,
            'settings' => [
                Swoole\Constant::OPTION_WORKER_NUM => 8,
                Swoole\Constant::OPTION_TASK_WORKER_NUM => 4,
                Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => false,
                Swoole\Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_WARNING,
                Swoole\Constant::OPTION_MAX_WAIT_TIME => 60,
                Swoole\Constant::OPTION_TASK_ENABLE_COROUTINE => true,
                Swoole\Constant::OPTION_PID_FILE => $opt['project_dir'].'/var/server.pid',
                Swoole\Constant::OPTION_LOG_FILE => $opt['project_dir'].'/var/log/prod_server.log',
            ],
        ],
        'tcp' => [
            'host' => '127.0.0.1',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
        ],
        'cache_table' => [
            'size' => 700,
            'column_length' => 3000,
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
