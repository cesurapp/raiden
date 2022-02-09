<?php

namespace App;

use Swoole\Http\Server;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected Server $server;

    public function setServer(Server $server): void
    {
        $this->server = $server;
    }

    public function getServer(): Server
    {
        return $this->server;
    }
}
