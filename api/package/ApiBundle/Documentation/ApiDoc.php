<?php

namespace Package\ApiBundle\Documentation;

/**
 * Api Documentation Generator.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class ApiDoc
{
    public function __construct(
        protected string $desc = '',
        protected string $dto = '',
        protected array $query = [],
        protected array $post = [],
        protected array $rSuccess = [],
        protected array $rException = [],
        protected bool $hidden = false
    ) {
    }
}
