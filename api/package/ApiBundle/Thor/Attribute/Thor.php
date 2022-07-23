<?php

namespace Package\ApiBundle\Thor\Attribute;

/**
 * Thor Api Documentation Generator.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class Thor
{
    public function __construct(
        protected string $group = '',
        protected string $groupDesc = '',
        protected string $desc = '',
        protected array $query = [],
        protected array $request = [],
        protected array $header = [],
        protected array $response = [],
        protected string $dto = '',
        protected bool $hidden = false,
        protected bool $paginate = false,
        protected bool $requireAuth = false,
        protected int $order = 0,
    ) {
    }
}
