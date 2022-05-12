<?php

namespace Package\ApiBundle\EventListener;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Package\ApiBundle\Response\ApiResourceLocator;
use Package\ApiBundle\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Controller Result Convert to Symfony Response.
 */
class ControllerResultConverter implements EventSubscriberInterface
{
    public function __construct(private readonly ApiResourceLocator $resourceLocator)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        /** @var ApiResponse $apiResponse */
        $apiResponse = $event->getControllerResult();

        // Paginate Query
        if ($apiResponse->isPaginate()) {
            $this->{'paginate'.$apiResponse->getPaginate()['type']}($event->getRequest(), $apiResponse);
        }

        // Process Api Response
        $this->processResource($apiResponse);

        // Add Type
        $apiResponse->addData('type', $apiResponse->getType()->name);

        // Create Response
        $response = new JsonResponse($apiResponse->getData(), $apiResponse->getStatus(), $apiResponse->getHeaders());

        // HTTP Cache
        if ($apiResponse->isHTTPCache()) {
            $response->setCache($apiResponse->getHTTPCache());
        }

        $event->setResponse($response);
    }

    /**
     * Paginate Query to Offset.
     */
    private function paginateOffset(Request $request, ApiResponse $apiResponse): void
    {
        $config = $apiResponse->getPaginate();
        $max = $config['max'];
        $page = $request->query->getInt('page', 1);

        // Paginate
        $apiResponse->getQuery()?->setFirstResult(($page - 1) * $max)->setMaxResults($max + 1);
        $paginator = new Paginator($apiResponse->getQuery(), $config['fetchJoin']);
        $iterator = $paginator->getIterator();

        $pager = [
            'max' => $max,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $iterator->count() > $max ? $page + 1 : null,
            'current' => $page,
        ];

        if ($config['total']) {
            $pager['total'] = $paginator->count();
        }

        // Append Pager Data
        $apiResponse->addData('data', array_slice((array) $iterator, 0, $max));
        $apiResponse->addData('pager', $pager);
    }

    /**
     * Paginate Query to Cursor.
     */
    private function paginateCursor(Request $request, ApiResponse $apiResponse): void
    {
    }

    /**
     * Process Object Array Serialize.
     */
    private function processResource(ApiResponse $apiResponse): void
    {
        if (!$apiResponse->getResource()) {
            $apiResponse->setData(['data' => $apiResponse->getData()]);

            return;
        }

        if ($apiResponse->isPaginate()) {
            $apiResponse->addData(
                'data',
                $this->resourceLocator->process($apiResponse->getData()['data'], $apiResponse->getResource())
            );

            return;
        }

        $data = $apiResponse->getData();
        if (is_object($data) || is_array($data)) {
            $apiResponse->setData([
                'data' => $this->resourceLocator->process($data, $apiResponse->getResource()),
            ]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onKernelView']];
    }
}
