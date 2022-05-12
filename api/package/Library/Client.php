<?php

namespace Package\Library;

use Swoole\Coroutine\Http\Client as SwooleClient;
use Swoole\Coroutine\Http\Client\Exception;

/**
 * Swoole Coroutine Based Http Client.
 *
 * @author  Ramazan APAYDIN
 */
class Client
{
    /**
     * Coroutine Client.
     */
    public SwooleClient $client;

    /**
     * Parsed URI.
     */
    private string $requestUri;

    /**
     * Global Headers.
     */
    private array $headers = [
        // 'Host' => '',
        // 'User-Agent' => 'Chrome/49.0.2587.3',
    ];

    /**
     * Coroutine Client Options.
     */
    private array $options = [
        'method' => 'GET',
        'reconnect' => 1,
        'timeout' => 4,
        'defer' => false,
        'keep_alive' => false,
        'websocket_mask' => false,
        'websocket_compression' => false,
        'http_compression' => false,
        'body_decompression' => true,
    ];

    public function __construct(string $uri)
    {
        // Set Header
        $this->headers['User-Agent'] = $_ENV['APP_NAME'];

        // Create Client
        $info = parse_url($uri);
        if ('http' === $info['scheme']) {
            $this->client = new SwooleClient($info['host'], $info['port'] ?? 80, false);
        } elseif ('https' === $info['scheme']) {
            $this->client = new SwooleClient($info['host'], $info['port'] ?? 443, true);
        } else {
            throw new Exception('unknown scheme "'.$info['scheme'].'"');
        }

        // Parse Request Uri
        $this->requestUri = $info['path'] ?? '/';
        if (!empty($info['query'])) {
            $this->requestUri .= '?'.$info['query'];
        }

        // Set Defaults
        $this->client->setHeaders($this->headers);
        $this->client->set($this->options);
    }

    public function get(?array $query = null): SwooleClient
    {
        if ($query) {
            $this->setQuery($query);
        }

        $this->client->setMethod('GET');
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    public function post(string|array $data = []): SwooleClient
    {
        $this->client->setMethod('POST');
        $this->setData($data);
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    public function put(string|array $data = []): SwooleClient
    {
        $this->client->setMethod('PUT');
        $this->setData($data);
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    public function patch(string|array $data = []): SwooleClient
    {
        $this->client->setMethod('PATCH');
        $this->setData($data);
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    public function delete(?array $query = null): SwooleClient
    {
        if ($query) {
            $this->setQuery($query);
        }

        $this->client->setMethod('DELETE');
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    public function setQuery(array $query = [], bool $clearCurrent = false): self
    {
        if ($clearCurrent) {
            $this->requestUri = '/?'.urldecode(http_build_query($query));

            return $this;
        }

        $current = [];
        parse_str(ltrim($this->requestUri, '?\\/'), $current);
        $this->requestUri = '/?'.urldecode(http_build_query(array_merge_recursive($current, $query)));

        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->client->setMethod($method);

        return $this;
    }

    public function setData(string|array $data): self
    {
        $this->client->setData($data);

        return $this;
    }

    public function setBasicAuth(string $username, string $password): self
    {
        $this->client->setBasicAuth($username, $password);

        return $this;
    }

    public function setHeaders(array $headers, bool $reset = false): self
    {
        $this->client->setHeaders($reset ? $headers : array_merge($this->headers, $headers));

        return $this;
    }

    public function setCookies(array $cookies): self
    {
        $this->client->setCookies($cookies);

        return $this;
    }

    public function setDefer(bool $defer): self
    {
        $this->client->setDefer($defer);

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->client->set($options);

        return $this;
    }

    public function setProxy(string $host, int $port, string $username, string $password): self
    {
        $this->client->set([
            'http_proxy_host' => $host,
            'http_proxy_port' => $port,
            'http_proxy_user' => $username,
            'http_proxy_password' => $password,
        ]);

        return $this;
    }

    public function setSock5Proxy(string $host, int $port, string $username, string $password): self
    {
        $this->client->set([
            'socks5_host' => $host,
            'socks5_port' => $port,
            'socks5_username' => $username,
            'socks5_password' => $password,
        ]);

        return $this;
    }

    /**
     * Execute Request.
     */
    public function execute(): SwooleClient
    {
        $this->client->execute($this->requestUri);

        return $this->client;
    }

    /**
     * Create Static.
     */
    public static function create(string $uri): self
    {
        return new self($uri);
    }
}
