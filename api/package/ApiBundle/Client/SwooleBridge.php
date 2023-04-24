<?php

namespace Package\ApiBundle\Client;

use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class SwooleBridge implements HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $client = SwooleClient::create($url)->setMethod($method)->setOptions($options);

        if (isset($options['headers'])) {
            $client->setHeaders($options['headers']);
        }
        if (isset($options['json'])) {
            $client->setJsonData($options['json']);
        }

        return new SwooleResponse($client->execute(), $url);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        $generator = static function () use ($responses): \Generator {
            yield $responses;
        };

        return new ResponseStream($generator());
    }

    public function withOptions(array $options): static
    {
        return $this;
    }
}
