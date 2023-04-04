<?php

namespace Package\SwooleBundle\Runtime\SwooleServer;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\Http\Server;
use OpenSwoole\Table;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class HttpServer extends Server
{
    public Table $appCache;

    public function __construct(private readonly HttpKernelInterface $application, private readonly array $options)
    {
        parent::__construct(
            $this->options['http']['host'],
            (int) $this->options['http']['port'],
            (int) $this->options['http']['mode'],
            (int) $this->options['http']['sock_type']
        );
        $this->set($this->options['http']['settings']);

        // Init Requests
        $this->on('request', [$this, 'onRequest']);

        // Init Memory Cache
        $this->initCache();

        $GLOBALS['httpServer'] = $this;
    }

    /**
     * Create Application Cache.
     */
    private function initCache(): void
    {
        $this->appCache = new Table($this->options['cache_table']['size']);
        $this->appCache->column('value', Table::TYPE_STRING, (int) $this->options['cache_table']['column_length']);
        $this->appCache->column('expr', Table::TYPE_INT);
        $this->appCache->column('key', Table::TYPE_STRING, 350);
        $this->appCache->create();
    }

    /**
     * Handle Request.
     */
    public function onRequest(Request $request, Response $response): void
    {
        $sfRequest = $this->convertSwooleRequest($request);
        $sfResponse = $this->application->handle($sfRequest);
        $this->reflectSymfonyResponse($sfResponse, $response);

        // Application Terminate
        if ($this->application instanceof TerminableInterface) {
            $this->application->terminate($sfRequest, $sfResponse);
        }
    }

    private function convertSwooleRequest(Request $request): SymfonyRequest
    {
        $sfRequest = new SymfonyRequest(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            array_change_key_case($request->server ?? [], CASE_UPPER),
            $request->rawContent()
        );
        $sfRequest->headers = new HeaderBag($request->header ?? []);

        return $sfRequest;
    }

    private function reflectSymfonyResponse(SymfonyResponse $sfResponse, Response $response): void
    {
        foreach ($sfResponse->headers->all() as $name => $values) {
            $response->header($name, $values);
        }

        $response->status($sfResponse->getStatusCode());

        switch (true) {
            case $sfResponse instanceof BinaryFileResponse && $sfResponse->headers->has('Content-Range'):
            case $sfResponse instanceof StreamedResponse:
                ob_start(static function ($buffer) use ($response) {
                    $response->write($buffer);

                    return '';
                });
                $sfResponse->sendContent();
                ob_end_clean();
                $response->end();
                break;
            case $sfResponse instanceof BinaryFileResponse:
                $response->sendfile($sfResponse->getFile()->getPathname());
                break;
            default:
                $response->end($sfResponse->getContent());
        }
    }
}
