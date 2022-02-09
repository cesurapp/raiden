<?php

namespace Package\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Json Request Body to Array Convert
 */
class RequestListener implements EventSubscriberInterface
{
    /**
     * @throws \JsonException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->headers->get('Content-Type') === 'application/json' && $content = $request->getContent()) {
            $request->request->add(
                json_decode($content, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR)
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
    }
}