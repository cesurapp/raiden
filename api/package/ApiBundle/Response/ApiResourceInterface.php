<?php

namespace Package\ApiBundle\Response;

/**
 * Entity API Resource.
 */
interface ApiResourceInterface
{
    public function toArray(mixed $item): array;
}
