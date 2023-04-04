<?php

namespace Package\SwooleBundle\Task;

interface TaskInterface
{
    public function __invoke(string $data): mixed;
}
