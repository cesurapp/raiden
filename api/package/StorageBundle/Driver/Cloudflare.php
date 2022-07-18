<?php

namespace Package\StorageBundle\Driver;

use Package\StorageBundle\Client\AbstractDriver;
use Package\StorageBundle\Client\SimpleS3Client;

class Cloudflare extends AbstractDriver
{
    public function __construct(
        protected string $accessKey,
        protected string $secretKey,
        protected string $bucket,
        protected string $root,
        protected string $endPoint = '',
        protected string $region = '',
    ) {
        $this->client = new SimpleS3Client([
            'accessKeyId' => $accessKey,
            'accessKeySecret' => $secretKey,
            'region' => $this->region,
            'endpoint' => $this->endPoint,
            'pathStyleEndpoint' => true,
        ]);
    }
}
