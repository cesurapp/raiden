<?php

namespace Package\ApiBundle\Contract;

/**
 * Entity API Resource
 */
interface ApiResourceInterface
{
    public const DEFAULT = 'default';
    public const ADMIN = 'admin';

    /**
     * Default User Resource
     */
    public function defaultResource(): array;
}