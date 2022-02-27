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
        protected array $get = [],
        protected array $post = [],
        protected array $header = [],
        protected array $success = [],
        protected array $exception = [],
        protected string $resource = '',
        protected bool $hidden = false,
        protected bool $paginate = false,
        protected bool $paginateCursor = false,
        protected bool $requireAuth = false
    ) {
    }
}
