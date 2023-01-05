<?php

namespace Package\ApiBundle\EventListener;

use App\Admin\Core\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Append Request Attribute.
 */
readonly class GlobalRequestAttribute implements EventSubscriberInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->isMethod('PUT')) {
            /** @var User|null $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            if ($user) {
                $request->attributes->set('uid', $user->getId()?->toBase32());
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest']];
    }
}
