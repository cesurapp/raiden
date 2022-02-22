<?php

namespace Package\ApiBundle\Response;

use Package\ApiBundle\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ApiResourceLocator
{
    public function __construct(private ServiceLocator $locator)
    {
    }

    public function get(string $resource): ApiResourceInterface
    {
        if ($this->locator->has($resource)) {
            return $this->locator->get($resource);
        }

        throw new ResourceNotFoundException();
    }

    public function process(object|array $object, ?string $resource = null): mixed
    {
        if (is_object($object)) {
            return $this->get($resource)->handle($object); // @phpstan-ignore-line
        }

        return null;
    }
}
