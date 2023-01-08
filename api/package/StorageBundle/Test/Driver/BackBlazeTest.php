<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\BackBlaze;

class BackBlazeTest extends S3Base
{
    protected function init(): void
    {
        $accessKey = $_SERVER['BACKBLAZE_ACCESS_KEY'] ?? '';
        $secretKey = $_SERVER['BACKBLAZE_SECRET'] ?? '';
        $bucket = $_SERVER['BACKBLAZE_BUCKET'] ?? 'unit-test';

        if (!$accessKey) {
            $this->markTestSkipped();
        }

        $this->client = new BackBlaze($accessKey, $secretKey, $bucket, '/', '', BackBlaze::US_WEST_004);
    }
}
