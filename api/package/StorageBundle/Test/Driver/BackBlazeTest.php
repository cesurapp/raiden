<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\BackBlaze;
use Package\StorageBundle\Driver\S3;

class BackBlazeTest extends S3Base
{
    protected function init(): void
    {
        if ('backblaze' !== $_SERVER['TEST_STORAGE']) {
            $this->markTestSkipped();
        }

        $this->root = 'root';
        $key = $_SERVER['BACKBLAZE_ACCESS_KEY'] ?? '';
        $secret = $_SERVER['BACKBLAZE_SECRET'] ?? '';
        $bucket = 'phpunit-test';

        $this->object = new BackBlaze($this->root, $key, $secret, $bucket, BackBlaze::US_WEST_004, S3::ACL_PRIVATE);
    }

    protected function getAdapterName(): string
    {
        return 'BackBlaze B2 Storage';
    }

    protected function getAdapterDescription(): string
    {
        return 'BackBlaze B2 Storage';
    }
}
