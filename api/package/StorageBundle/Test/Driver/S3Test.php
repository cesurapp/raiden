<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\S3;

class S3Test extends S3Base
{
    protected function init(): void
    {
        if ('s3' !== $_SERVER['TEST_STORAGE']) {
            $this->markTestSkipped();
        }

        $this->root = '/root';
        $key = $_SERVER['S3_ACCESS_KEY'] ?? '';
        $secret = $_SERVER['S3_SECRET'] ?? '';
        $bucket = 'phpunit-test';

        $this->object = new S3($this->root, $key, $secret, $bucket, S3::AP_SOUTH_1, S3::ACL_PRIVATE);
    }

    protected function getAdapterName(): string
    {
        return 'S3 Storage';
    }

    protected function getAdapterDescription(): string
    {
        return 'S3 Bucket Storage drive for AWS or on premise solution';
    }
}
