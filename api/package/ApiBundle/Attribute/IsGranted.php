<?php

namespace Package\ApiBundle\Attribute;

/**
 * Thor Api Resource Documentation.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class IsGranted
{
    public function __construct(protected array $roles = [])
    {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
