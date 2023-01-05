<?php

namespace Package\ApiBundle\Response;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Symfony Global Response | Paginator.
 */
class ApiResponse
{
    private int $code = 200;
    private array $headers = [];
    private mixed $data = [];
    private array $options = [];
    private ?string $resource = null;
    private Query|QueryBuilder|null $query = null;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

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

    public function setCorsOrigin(?string $domain = null): self
    {
        $this->headers['Access-Control-Allow-Origin'] = $domain ?? getenv('APP_DEFAULT_URI');

        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): self
    {
        if (!is_array($data)) {
            $data = ['data' => $data];
        }

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

    /**
     * Check HTTP Proxy Cache.
     */
    public function isHTTPCache(): bool
    {
        return $this->options['httpCache'] ?? false;
    }

    public function getHTTPCache(): ?array
    {
        return $this->options['httpCache'] ?? null;
    }

    /**
     * Enable HTTP Proxy Cache.
     */
    public function setHTTPCache(int $lifetime = 60, ?array $tags = null): self
    {
        $this->options['httpCache'] = [
            'public' => true,
            'max_age' => $lifetime,
            's_maxage' => $lifetime,
        ];

        if ($tags) {
            $this->headers['Cache-Tag'] = implode(',', array_map(static fn ($tag) => hash('xxh3', (string) $tag), $tags));
        }

        $this->headers[AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER] = true;

        return $this;
    }

    public function getOptions(string $key): mixed
    {
        return $this->options[$key];
    }

    public function addMessage(string $message, MessageType $messageType = MessageType::SUCCESS): self
    {
        if (!isset($this->data['message'][$messageType->value])) {
            $this->data['message'][$messageType->value] = [];
        }

        $this->data['message'][$messageType->value][] = $message;

        return $this;
    }

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

    public static function create(int $code = 200): self
    {
        return (new self())->setCode($code);
    }

    /**
     * Process Object Array Serialize.
     */
    public function processResponse(Request $request, ApiResourceLocator $resourceLocator, TranslatorInterface $translator): JsonResponse
    {
        // Init Paginator
        if ($this->isPaginate()) {
            $this->paginate($request);
        }

        // Process Resource
        array_walk_recursive($this->data, function (&$data) use ($resourceLocator) {
            if (is_object($data)) {
                $data = $resourceLocator->process($data, $this->getResource());
            }
        });

        // Message Translator
        if (isset($this->data['message'])) {
            foreach ($this->data['message'] as $type => $messages) {
                $this->data['message'][$type] = array_map(static fn ($message) => $translator->trans($message), $messages);
            }
        }

        // Create Response
        $response = new JsonResponse($this->getData(), $this->getCode(), $this->getHeaders());

        // HTTP Cache
        if ($this->isHTTPCache()) {
            $response->setCache($this->getHTTPCache());
        }

        return $response;
    }

    /**
     * Paginate Query to Offset.
     */
    private function paginate(Request $request): void
    {
        $config = $this->getPaginate();
        $page = $request->query->getInt('page', 1);

        // Paginate
        $this->getQuery()?->setFirstResult(($page - 1) * $config['max'])->setMaxResults($config['max'] + 1);
        $paginator = new Paginator($this->getQuery(), $config['fetchJoin']);
        /** @var \ArrayIterator $iterator */
        $iterator = $paginator->getIterator();

        $pager = [
            'max' => $config['max'],
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $iterator->count() > $config['max'] ? $page + 1 : null,
            'current' => $page,
        ];

        if ($config['total']) {
            $pager['total'] = $paginator->count();
        }

        // Append Pager Data
        $this->addData('data', array_slice((array) $iterator, 0, $config['max']));
        $this->addData('pager', $pager);
    }
}
