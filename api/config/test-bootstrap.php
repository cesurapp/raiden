<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    if (file_exists(dirname(__DIR__).'/.env.local')) {
        $env = dirname(__DIR__).'/.env.local';
    }
    (new Dotenv())->bootEnv($env ?? dirname(__DIR__).'/.env');
}
