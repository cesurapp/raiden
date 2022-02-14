<?php

namespace Package\SwooleBundle\Cron;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class CronConstantNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Cron TIME constant not found!', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
