<?php

namespace Package\ApiBundle\Thor\Attribute;

/**
 * Thor Api Resource Documentation.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class ThorResource
{
    public function __construct(protected array $data = [])
    {
    }
}
