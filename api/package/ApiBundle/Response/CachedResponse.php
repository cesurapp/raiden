<?php

namespace Package\ApiBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Proxy Cache Response.
 */
class CachedResponse extends JsonResponse
{
    public static function create(array $data, int $ageSecond = 60, ?array $tags = null): CachedResponse
    {
        $response = new self($data, 200, []);

        // Enable Session Cache
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

        // Set Proxy Cache Timeout
        $response->setMaxAge($ageSecond)->setSharedMaxAge($ageSecond);

        // Add Cache Tags
        if ($tags) {
            $response->headers->set('Cache-Tag', implode(',', array_map(static function ($tag) {
                return hash('crc32b', (string) $tag);
            }, $tags)));
        }

        return $response;
    }
}
