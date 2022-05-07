<?php

namespace Package\StorageBundle\Test;

use Package\StorageBundle\Storage\Storage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StorageTest extends WebTestCase
{
    public function testStorage(): void
    {
        $container = self::createClient()->getContainer();

        $this->assertTrue($container->has(Storage::class));
    }
}
