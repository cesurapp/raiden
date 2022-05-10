<?php

namespace Package\SwooleBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{
    public function testTaskHandler(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
    }
}
