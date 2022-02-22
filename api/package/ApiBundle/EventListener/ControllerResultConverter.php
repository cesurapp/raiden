<?php

namespace Package\ApiBundle\EventListener;

use Doctrine\ORM\Tools\Pagination\Paginator;
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
    public const PAGER_MAX = 20;

    public function onKernelView(ViewEvent $event): void
    {
        /** @var ApiResponse $apiResponse */
        $apiResponse = $event->getControllerResult();

        // Paginate Query
        if ($apiResponse->isPaginate() && $apiResponse->getQuery()) {
            $this->{'paginate'.$apiResponse->getPaginate()->name}($event->getRequest(), $apiResponse);
        }

        // Process Api Response
        $this->processResource($apiResponse);

        // Create Response
        $response = new JsonResponse([
            'type' => $apiResponse->getType()->name,
            'data' => $apiResponse->getData(),
        ], $apiResponse->getStatus(), $apiResponse->getHeaders());

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
        /*$result['options']['pagerMax'] ??= self::PAGER_MAX;
        $result['options']['pagerPage'] ??= $request->query->getInt('page', 1);

        // Paginate
        $result['data']
            ->setFirstResult($result['options']['pagerPage'] - 1)
            ->setMaxResults($result['options']['pagerMax'] + 1);

        $paginator = new Paginator($result['data'], $result['options']['fetchJoin'] ?? true);
        $iterator = $paginator->getIterator();

        $data['data'] = (array) $iterator;
        $data['pager'] = [
            'max' => $result['options']['pagerMax'],
            'prevPage' => $result['options']['pagerPage'] > 1 ? $result['options']['pagerPage'] - 1 : null,
            'nextPage' => $iterator->count(
            ) > $result['options']['pagerMax'] ? $result['options']['pagerPage'] + 1 : null,
            'currentPage' => $result['options']['pagerPage'],
        ];*/
    }

    /**
     * Paginate Query to Cursor.
     */
    private function paginateCursor(Request $request, ApiResponse $apiResponse): void
    {
    }

    /**
     * Process Object Array Serialzie.
     */
    private function processResource(ApiResponse $apiResponse): void
    {
        /*$data['data'] ??= $result['data'];
        if (!is_array($data['data'])) {
            return;
        }

        array_walk_recursive($data['data'], static function ($item, $key) {
            if (is_object($item)) {
                dump($key, $item);
            }
        });*/
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onKernelView']];
    }
}
