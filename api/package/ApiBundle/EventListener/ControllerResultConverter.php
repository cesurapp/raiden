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

    /**
     * Response Data.
     */
    private array $data = [];

    public function onKernelView(ViewEvent $event): void
    {
        $this->data = [];
        $result = $event->getControllerResult();

        // Paginate Data
        $this->paginateData($event->getRequest(), $result);

        // Prepare Data
        $this->prepareData($event->getRequest());

        $this->data['type'] = $result['type'];

        // Create Response
        $event->setResponse(new JsonResponse(
            $this->data,
            $result['options']['status'] ?? 200,
            $result['options']['headers'] ?? [],
        ));
    }

    private function paginateData(Request $request, mixed $data): void
    {
        if (!isset($data['options']['pager'])) {
            return;
        }

        $data['options']['pagerMax'] ??= self::PAGER_MAX;
        $data['options']['pagerPage'] ??= $request->query->getInt('page', 1);

        // Paginate
        $data['data']
            ->setFirstResult($data['options']['pagerPage'] - 1)
            ->setMaxResults($data['options']['pagerMax'] + 1);

        $paginator = new Paginator($data['data'], $data['options']['fetchJoin'] ?? true);
        $iterator = $paginator->getIterator();

        $this->data['data'] = (array) $iterator;
        $this->data['pager'] = [
            'max' => $data['options']['pagerMax'],
            'prevPage' => $data['options']['pagerPage'] > 1 ? $data['options']['pagerPage'] - 1 : null,
            'nextPage' => $iterator > $data['options']['pagerMax'] ? $data['options']['pagerPage'] + 1 : null,
            'currentPage' => $data['options']['pagerPage'],
        ];
    }

    private function prepareData(mixed $data): array|string|int|bool
    {
        // Process Object Resource
        return array_walk_recursive($data['data'], static function ($item) {
        });
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onKernelView']];
    }
}
