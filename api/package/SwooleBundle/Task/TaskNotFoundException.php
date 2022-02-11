<?php

namespace Package\SwooleBundle\Task;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class TaskNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Task not found!', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
