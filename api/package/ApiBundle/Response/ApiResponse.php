<?php

namespace Package\ApiBundle\Response;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Symfony Global Response | Paginator.
 */
class ApiResponse
{
    private ResponseTypeEnum $type = ResponseTypeEnum::ApiResult;

    private int $status = 200;

    private array $headers = [];

    private array|string|int|bool|object|null $data = [];

    private Query|QueryBuilder|null $query = null;

    private array $options = [];

    private ?string $resource = null;

    public static function create(ResponseTypeEnum $type = ResponseTypeEnum::ApiResult, int $status = 200): self
    {
        return (new self())->setType($type)->setStatus($status);
    }

    public static function createError(ResponseTypeEnum $type = ResponseTypeEnum::ApiError, int $status = 403): self
    {
        return (new self())->setType($type)->setStatus($status);
    }

    public static function createInfo(ResponseTypeEnum $type = ResponseTypeEnum::ApiInfo, int $status = 200): self
    {
        return (new self())->setType($type)->setStatus($status);
    }

    public static function createWarn(ResponseTypeEnum $type = ResponseTypeEnum::ApiWarning, int $status = 200): self
    {
        return (new self())->setType($type)->setStatus($status);
    }

    public function getType(): ResponseTypeEnum
    {
        return $this->type;
    }

    public function setType(ResponseTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function getData(): array|bool|int|string|object|null
    {
        return $this->data;
    }

    public function setData(array|bool|int|string|object|null $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addData(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function getQuery(): Query|QueryBuilder|null
    {
        return $this->query;
    }

    public function setQuery(Query|QueryBuilder $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function isPaginate(): bool
    {
        return isset($this->options['pager']) && null !== $this->query;
    }

    public function getPaginate(): ?array
    {
        return $this->options['pager'] ?? null;
    }

    public function setPaginate(?int $max = 20, bool $total = false, bool $fetchJoin = true, bool $cursor = false): self
    {
        $this->options['pager'] = [
            'type' => $cursor ? 'Cursor' : 'Offset',
            'max' => $max,
            'total' => $total,
            'fetchJoin' => $fetchJoin,
        ];

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resourceClass): self
    {
        $this->resource = $resourceClass;

        return $this;
    }

    public function isHTTPCache(): bool
    {
        return $this->options['httpCache'] ?? false;
    }

    public function getHTTPCache(): ?array
    {
        return $this->options['httpCache'] ?? null;
    }

    public function setHTTPCache(int $lifetime = 60, ?array $tags = null): self
    {
        $this->options['httpCache'] = [
            'public' => true,
            'max_age' => $lifetime,
            's_maxage' => $lifetime,
        ];

        if ($tags) {
            $this->headers['Cache-Tag'] = implode(',', array_map(fn ($tag) => hash('crc32b', (string) $tag), $tags));
        }

        $this->headers[AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER] = true;

        return $this;
    }

    public function getOptions(string $key): mixed
    {
        return $this->options[$key];
    }

    /**
     * Download Binary File.
     */
    public static function file(
        \SplFileInfo|string $path,
        string $fileName = '',
        string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    ): BinaryFileResponse {
        return (new BinaryFileResponse($path))->setContentDisposition($disposition, $fileName);
    }

    /**
     * Download Large File.
     */
    public static function fileLarge(string $filePath, string $fileName = null): StreamedResponse
    {
        $file = new File($filePath);

        return new StreamedResponse(static function () use ($filePath) {
            $output = fopen('php://output', 'wb+');
            $handle = fopen($filePath, 'rb');

            while (!feof($handle)) {
                fwrite($output, fread($handle, 2048));
            }

            fclose($output);
            fclose($handle);
        }, 200, [
            'Content-Type' => $file->getMimeType(),
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $fileName ?? $file->getFilename()
            ),
        ]);
    }
}
