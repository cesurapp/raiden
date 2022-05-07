<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\DOSpaces;
use Package\StorageBundle\Driver\S3;

class DOSpacesTest extends S3Base
{
    protected function init(): void
    {
        if ('dospaces' !== $_SERVER['TEST_STORAGE']) {
            $this->markTestSkipped();
        }

        $this->root = '/root';
        $key = $_SERVER['DO_ACCESS_KEY'] ?? '';
        $secret = $_SERVER['DO_SECRET'] ?? '';
        $bucket = 'phpunit-test';

        $this->object = new DOSpaces($this->root, $key, $secret, $bucket, DOSpaces::NYC3, S3::ACL_PUBLIC_READ);
    }

    protected function getAdapterName(): string
    {
        return 'Digitalocean Spaces Storage';
    }

    protected function getAdapterDescription(): string
    {
        return 'Digitalocean Spaces Storage';
    }
}
