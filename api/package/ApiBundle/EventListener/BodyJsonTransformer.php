<?php

namespace Package\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Json Request Body to Array Convert.
 */
class BodyJsonTransformer implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $content = $request->getContent();

        if ((str_starts_with($content, '{') && str_ends_with($content, '}')) ||
            (str_starts_with($content, '[') && str_ends_with($content, ']'))) {
            try {
                $request->request->add(json_decode($content, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR));
            } catch (\Exception $exception) {
                throw new BadRequestHttpException('Bad JSON Content.');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
    }
}
