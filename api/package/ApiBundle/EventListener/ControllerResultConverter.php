<?php

namespace Package\ApiBundle\EventListener;

use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $result = $event->getControllerResult();
        $data = ['type' => $result['type']];

        // Paginate Data
        $this->paginateData($event->getRequest(), $data, $result);

        // Prepare Data
        $this->prepareData($data, $result);

        // Create Response
        $event->setResponse(
            new JsonResponse(
                $data,
                $result['options']['status'] ?? 200,
                $result['options']['headers'] ?? [],
            )
        );
    }

    /**
     * Paginate Query.
     */
    private function paginateData(Request $request, array &$data, mixed $result): void
    {
        if (!isset($result['options']['pager'])) {
            return;
        }

        $result['options']['pagerMax'] ??= self::PAGER_MAX;
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
        ];
    }

    /**
     * Serialize Object.
     */
    private function prepareData(array &$data, mixed $result): void
    {
        $data['data'] ??= $result['data'];
        if (!is_array($data['data'])) {
            return;
        }

        array_walk_recursive($data['data'], static function ($item, $key) {
            if (is_object($item)) {
                dump($key, $item);
            }
        });
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onKernelView']];
    }
}
