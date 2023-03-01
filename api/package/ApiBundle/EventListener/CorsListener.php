<?php

namespace Package\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Cors Handler.
 */
class CorsListener implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!in_array($event->getRequest()->getRealMethod(), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $event->setResponse(new JsonResponse([]));
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
            KernelEvents::RESPONSE => ['onKernelResponse', 9999],
        ];
    }
}
