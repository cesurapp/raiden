<?php

namespace Package\ApiBundle\Attribute;

/**
 * Api Documentation Generator.
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class ApiDoc
{
    public function __construct(
        protected string $description,
        protected ?string $apiDto = null,
        protected array $query = [],
        protected array $body = [],
        protected array $response = []
    ) {
    }
}
