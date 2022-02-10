<?php

namespace Package\SwooleBundle\Task;

interface TaskInterface
{
    public function __invoke(string|array|bool|null $data): void;
}
