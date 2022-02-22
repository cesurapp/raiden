<?php

namespace Package\ApiBundle\Response;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Symfony Global Response | Paginator.
 */
class ApiResponse
{
    private ResponseTypeEnum $type = ResponseTypeEnum::ApiResult;

    private int $status = 200;

    private array $headers = [];

    private array|string|int|bool|null $data = null;

    private Query|QueryBuilder|null $query = null;

    private array $options = [];

    public static function create(ResponseTypeEnum $type = ResponseTypeEnum::ApiResult, int $status = 200): self
    {
        return (new self())
            ->setType($type)
            ->setStatus($status);
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

    public function getData(): array|bool|int|string|null
    {
        return $this->data;
    }

    public function setData(array|bool|int|string|null $data): self
    {
        $this->data = $data;

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
        return isset($this->options['pager']);
    }

    public function getPaginate(): ResponsePaginateEnum
    {
        return $this->options['pager'];
    }

    public function setPaginate(?int $max = 20, ResponsePaginateEnum $paginate = ResponsePaginateEnum::Offset): self
    {
        $this->options['pager'] = $paginate;
        $this->options['pagerMax'] = $max;

        return $this;
    }

    public function isHTTPCache(): bool
    {
        return isset($this->options['http_cache']);
    }

    public function getHTTPCache(): array
    {
        return $this->options['http_cache_options'];
    }

    public function setHTTPCache(int $lifetime = 60, ?array $tags = null): self
    {
        $this->options['http_cache'] = true;
        $this->options['http_cache_options'] = [
            'max-age' => $lifetime,
            'public' => '',
            's-maxage' => $lifetime,
        ];
        if ($tags) {
            $this->headers['Cache-Tag'] = implode(',', array_map(fn ($tag) => hash('crc32b', (string) $tag), $tags));
        }

        return $this;
    }

    /* public static function create(mixed $data, int $status = 200, array $headers = []): array

     public static function paginate(QueryBuilder|Query $query, ?int $max = null, array $headers = []): array
     {
         return [
             'type' => 'ApiResult',
             'data' => $query,
             'options' => [
                 'pager' => 'Offset',
                 'pagerMax' => $max,
                 'pagerPage' => null,
                 'fetchJoin' => true,
             ],
         ];
     }

     public static function paginateCursor(QueryBuilder|Query $query, ?int $max = null, array $headers = []): array
     {
         return [
             'type' => 'ApiResult',
             'data' => $query,
             'options' => [
                 'status' => 200,
                 'headers' => $headers,
                 'pager' => 'Cursor',
                 'pagerMax' => $max,
             ],
         ];
     }
*/

    /**
     * Download Binary File.
     */
    public static function file(\SplFileInfo|string $path, string $fileName = '', string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
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

    /*    public static function json(array $data, int $ageSecond = 60, ?array $tags = null): CachedResponse
        {
            // Enable Session Cache
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        }*/
}
