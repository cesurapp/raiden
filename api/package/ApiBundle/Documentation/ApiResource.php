<?php

namespace Package\ApiBundle\Documentation;

/**
 * Api Resource Documentation.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class ApiResource
{
    public function __construct(protected array $data = [])
    {
    }
}
