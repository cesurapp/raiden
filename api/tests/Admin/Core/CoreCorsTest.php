<?php

namespace App\Tests\Admin\Core;

use App\Tests\Setup\KernelTestCase;

class CoreCorsTest extends KernelTestCase
{
    public function testCorsRequest(): void
    {
        static::bootKernel();

        // Wrong Request Header
        $this->request('HEAD', '/');
        $this->assertEmpty($this->getContent());

        $this
            ->request('GET', '/')
            ->isHeaderEquals('access-control-allow-origin.0', '*')
            ->isHeaderEquals('access-control-allow-methods.0', 'GET,POST,PUT,PATCH,DELETE')
            ->isHeaderEquals('access-control-allow-headers.0', '*');
    }
}
