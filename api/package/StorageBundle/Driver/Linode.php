<?php

namespace Package\StorageBundle\Driver;

/**
 * @see https://github.com/utopia-php/storage
 */
class Linode extends S3
{
    /**
     * Regions constants.
     */
    public const EU_CENTRAL_1 = 'eu-central-1';
    public const US_SOUTHEAST_1 = 'us-southeast-1';
    public const US_EAST_1 = 'us-east-1';
    public const AP_SOUTH_1 = 'ap-south-1';

    /**
     * Object Storage Constructor.
     */
    public function __construct(string $root, string $accessKey, string $secretKey, string $bucket, string $region = self::EU_CENTRAL_1, string $acl = self::ACL_PRIVATE)
    {
        parent::__construct($root, $accessKey, $secretKey, $bucket, $region, $acl);
        $this->headers['host'] = $bucket.'.'.$region.'.'.'linodeobjects.com';
    }

    public function getName(): string
    {
        return 'Linode Object Storage';
    }

    public function getDescription(): string
    {
        return 'Linode Object Storage';
    }
}
