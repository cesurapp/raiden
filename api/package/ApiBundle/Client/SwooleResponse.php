<?php

namespace Package\ApiBundle\Client;

use OpenSwoole\Coroutine\Http\Client;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SwooleResponse implements ResponseInterface
{
    public function __construct(private readonly Client $client, private readonly string $uri)
    {
    }

    public function getStatusCode(): int
    {
        return $this->client->getStatusCode();
    }

    public function getHeaders(bool $throw = true): array
    {
        return array_map(static fn ($c) => [$c], $this->client->getHeaders() ?? []);
    }

    public function getContent(bool $throw = true): string
    {
        return $this->client->getBody();
    }

    public function toArray(bool $throw = true): array
    {
        if ('' === $content = $this->getContent($throw)) {
            throw new JsonException('Response body is empty.');
        }

        try {
            $content = json_decode($content, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonException($e->getMessage().sprintf(' for "%s"', 'asdsa'), $e->getCode());
        }

        if (!\is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, "%s" returned for "%s".', get_debug_type($content), $this->getInfo('url')));
        }

        return $content;
    }

    public function cancel(): void
    {
    }

    public function getInfo(string $type = null): mixed
    {
        $info = [
            'canceled' => false,
            'error' => $this->client->errMsg,
            'http_code' => $this->client->statusCode,
            'http_method' => $this->client->requestMethod,
            'redirect_count' => 0,
            'redirect_url' => null,
            'response_headers' => $this->client->getHeaders(),
            'start_time' => 0.0,
            'url' => $this->uri,
            'user_data' => $this->client->requestBody,
        ];

        return $info[$type] ?? $type;
    }

    public function __toString(): string
    {
        return $this->client->body ?? '';
    }
}
