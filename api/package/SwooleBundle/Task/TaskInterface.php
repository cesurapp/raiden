<?php

namespace Package\SwooleBundle\Task;

interface TaskInterface
{
    public function __invoke(mixed $data = null): void;
}
