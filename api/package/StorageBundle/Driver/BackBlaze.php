<?php

namespace Package\StorageBundle\Driver;

/**
 * @see https://github.com/utopia-php/storage
 */
class BackBlaze extends S3
{
    /**
     * Regions.
     */
    public const US_WEST_001 = 'us-west-001';
    public const US_WEST_002 = 'us-west-002';
    public const US_WEST_003 = 'us-west-003';
    public const US_WEST_004 = 'us-west-004';
    public const EU_CENTRAL_001 = 'eu-central-001';
    public const EU_CENTRAL_002 = 'eu-central-002';
    public const EU_CENTRAL_003 = 'eu-central-003';
    public const EU_CENTRAL_004 = 'eu-central-004';

    public function __construct(string $root, string $accessKey, string $secretKey, string $bucket, string $region = self::US_WEST_004, string $acl = self::ACL_PRIVATE)
    {
        parent::__construct($root, $accessKey, $secretKey, $bucket, $region, $acl);
        $this->headers['host'] = $bucket.'.'.'s3'.'.'.$region.'.backblazeb2.com';
    }

    public function getName(): string
    {
        return 'BackBlaze B2 Storage';
    }

    public function getDescription(): string
    {
        return 'BackBlaze B2 Storage';
    }
}
