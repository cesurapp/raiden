<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\S3;
use Package\StorageBundle\Driver\Wasabi;

class WasabiTest extends S3Base
{
    protected function init(): void
    {
        if ('wasabi' !== $_SERVER['TEST_STORAGE']) {
            $this->markTestSkipped();
        }

        $this->root = 'root';
        $key = $_SERVER['WASABI_ACCESS_KEY'] ?? '';
        $secret = $_SERVER['WASABI_SECRET'] ?? '';
        $bucket = 'phpunit-test';

        $this->object = new Wasabi($this->root, $key, $secret, $bucket, Wasabi::EU_CENTRAL_1, S3::ACL_PRIVATE);
    }

    protected function getAdapterName(): string
    {
        return 'Wasabi Storage';
    }

    protected function getAdapterDescription(): string
    {
        return 'Wasabi Storage';
    }
}
