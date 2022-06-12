<?php

namespace Package\ApiBundle\EventListener;

use Package\ApiBundle\Response\ApiResourceLocator;
use Package\ApiBundle\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
        if ($apiResponse instanceof ApiResponse) {
            $event->setResponse($apiResponse->processResponse($event->getRequest(), $this->resourceLocator));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onKernelView']];
    }
}
