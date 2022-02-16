<?php

namespace Package\ApiBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Proxy Cache Response.
 */
class CachedJsonResponse extends JsonResponse
{
    public static function create(array $data, int $ageSecond = 60): CachedJsonResponse
    {
        $response = new self($data, 200, []);

        // Disable Browser & Enable Proxy Cache Header
        $response->setCache([
            'no-cache' => true,
            'public' => true,
            'max_age' => $ageSecond,
            's-maxage' => $ageSecond,
        ]);

        return $response;
    }
}
