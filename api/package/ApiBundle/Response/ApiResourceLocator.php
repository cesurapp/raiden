<?php

namespace Package\ApiBundle\Response;

use Package\ApiBundle\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class ApiResourceLocator
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

    public function getResource(string $resource): array
    {
        return $this->get($resource)->toResource();
    }

    public function all(): array
    {
        return $this->locator->getProvidedServices();
    }

    public function process(object|array $object, ?string $resource = null, mixed $optionalData = null): ?array
    {
        $res = $this->get($resource);

        if (is_array($object)) {
            return array_map(static fn ($item) => $res->toArray($item, $optionalData), $object);
        }

        return $res->toArray($object, $optionalData);
    }
}
