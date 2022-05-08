<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\Linode;
use Package\StorageBundle\Driver\S3;

class LinodeTest extends S3Base
{
    protected function init(): void
    {
        $this->root = 'root';
        $key = $_SERVER['LINODE_ACCESS_KEY'] ?? '';
        $secret = $_SERVER['LINODE_SECRET'] ?? '';
        $bucket = 'phpunit-test';

        if (!$key) {
            $this->markTestSkipped();
        }

        $this->object = new Linode($this->root, $key, $secret, $bucket, Linode::EU_CENTRAL_1, S3::ACL_PRIVATE);
    }

    protected function getAdapterName(): string
    {
        return 'Linode Object Storage';
    }

    protected function getAdapterDescription(): string
    {
        return 'Linode Object Storage';
    }
}
