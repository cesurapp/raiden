<?php

namespace Package\Library\Test;

use Package\Library\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientTest extends WebTestCase
{
    public function testGet(): void
    {
        $client = Client::create('https://www.google.com')->get();
        $this->assertEquals(200, $client->statusCode);

        $client = Client::create('http://www.google.com')->get();
        $this->assertEquals(200, $client->statusCode);

        $client = Client::create('https://google.com')->get();
        $this->assertEquals(301, $client->statusCode);
        $this->assertEquals('GET', $client->requestMethod);
    }

    public function testGetQuery(): void
    {
        // SSL
        $client = Client::create('https://www.google.com')->setQuery([
            'filter' => [
                'test' => 1,
                'active' => 'ok',
            ],
        ]);
        $clientArr = (array) $client;
        $this->assertEquals('/?filter[test]=1&filter[active]=ok', $clientArr["\0Package\Library\Client\0requestUri"]);
        $this->assertEquals('www.google.com', $client->client->host);
        $this->assertEquals(true, $client->client->ssl);
        $this->assertEquals(443, $client->client->port);

        // HTTP
        $client = Client::create('http://www.google.com')->setQuery([
            'filter' => [
                'test' => 1,
                'active' => 'ok',
            ],
        ]);
        $clientArr = (array) $client;
        $this->assertEquals('/?filter[test]=1&filter[active]=ok', $clientArr["\0Package\Library\Client\0requestUri"]);
        $this->assertEquals('www.google.com', $client->client->host);
        $this->assertEquals(false, $client->client->ssl);
        $this->assertEquals(80, $client->client->port);
    }

    public function testPost(): void
    {
        $client = Client::create('https://www.google.com')->post([
            'data' => 'test',
        ]);

        $this->assertEquals(['data' => 'test'], $client->requestBody);
        $this->assertEquals(405, $client->statusCode);
        $this->assertEquals('POST', $client->requestMethod);
        $this->assertStringContainsString('<code>POST</code>', $client->body);
    }

    public function testPut(): void
    {
        $client = Client::create('https://www.google.com')->put([
            'data' => 'test',
        ]);

        $this->assertEquals(['data' => 'test'], $client->requestBody);
        $this->assertEquals(405, $client->statusCode);
        $this->assertEquals('PUT', $client->requestMethod);
        $this->assertStringContainsString('<code>PUT</code>', $client->body);
    }

    public function testPatch(): void
    {
        $client = Client::create('https://www.google.com')->patch([
            'data' => 'test',
        ]);

        $this->assertEquals(['data' => 'test'], $client->requestBody);
        $this->assertEquals(405, $client->statusCode);
        $this->assertEquals('PATCH', $client->requestMethod);
        $this->assertStringContainsString('<code>PATCH</code>', $client->body);
    }

    public function testDelete(): void
    {
        $client = Client::create('https://www.google.com')->delete();

        $this->assertEquals(true, $client->ssl);
        $this->assertEquals(405, $client->statusCode);
        $this->assertEquals('DELETE', $client->requestMethod);
        $this->assertStringContainsString('<code>DELETE</code>', $client->body);
    }
}
