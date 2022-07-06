<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class CoreTest extends AbstractWebTestCase
{
    public function testCorsRequest(): void
    {
        // Wrong Rquest Header
        $this->client()->request('HEAD', '/');
        $this->assertEmpty($this->client()->getResponse()->getContent());

        $this->client()->request('GET', '/');
        $this->assertEquals('*', $this->client()->getResponse()->headers->get('access-control-allow-origin'));
        $this->assertEquals('GET,POST,PUT,PATCH,DELETE', $this->client()->getResponse()->headers->get('access-control-allow-methods'));
        $this->assertEquals('content-type', $this->client()->getResponse()->headers->get('access-control-allow-headers'));
    }

    public function testStickyLocale(): void
    {
        $this->client()->request('GET', '/');
        $this->assertEquals('en_US', $this->client()->getRequest()->getLanguages()[0]);

        $this->client()->request('GET', '/', server: [
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR',
        ]);
        $this->assertEquals('tr_TR', $this->client()->getRequest()->getLanguages()[0]);
    }
}
