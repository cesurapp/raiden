<?php

namespace Package\ApiBundle\Response;

/**
 * Entity API Resource.
 */
interface ApiResourceInterface
{
    public function toArray(object $item): array;

    public function toResource(): array;
}
