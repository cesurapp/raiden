<?php

//asdasdsa

namespace App\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestTask implements TaskInterface
{
    public function __invoke(mixed $data = null): void
    {
        var_dump($data);
        sleep(1);
        //throw new NotFoundHttpException('404 not found');
    }
}
