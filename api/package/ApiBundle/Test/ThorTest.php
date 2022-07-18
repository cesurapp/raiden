<?php

namespace Package\ApiBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ThorTest extends WebTestCase
{
    public function testView(): void
    {
        $client = self::createClient();
        $client->request('GET', 'thor');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTypeScriptDownload(): void
    {
        $client = self::createClient();
        $client->request('GET', 'thor/latest/download');

        $this->assertEquals(true, $client->getResponse()->isOk());
    }
}
