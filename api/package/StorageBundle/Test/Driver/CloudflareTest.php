<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\Cloudflare;

class CloudflareTest extends S3Base
{
    protected function init(): void
    {
        $accessKey = $_SERVER['CLOUDFLARE_R2_ACCESS_KEY'] ?? '';
        $secretKey = $_SERVER['CLOUDFLARE_R2_SECRET'] ?? '';
        $endPoint = $_SERVER['CLOUDFLARE_R2_ENDPOINT'] ?? '';
        $bucket = 'unit-test';

        if (!$accessKey) {
            $this->markTestSkipped();
        }

        $this->client = new Cloudflare($accessKey, $secretKey, $bucket, '/', $endPoint);
    }
}
