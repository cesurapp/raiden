<?php

namespace Package\ApiBundle\EventListener;

use Package\ApiBundle\Attribute\IsGranted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Check Roles.
 */
class SecurityIsGranted implements EventSubscriberInterface
{
    public function __construct(private readonly AuthorizationCheckerInterface $checker)
    {
    }

    public function onControllerArgument(ControllerEvent $event): void
    {
        [$controller, $method] = $event->getController(); // @phpstan-ignore-line
        $refClass = new \ReflectionClass(get_class($controller));
        $permissions = $refClass->getMethod($method)->getAttributes(IsGranted::class);

        if (!$permissions) {
            return;
        }

        $permissions = $permissions[0]->getArguments();
        $permissions = $permissions[0] ?? $permissions['roles'];
        foreach ($permissions as $permission) {
            if (!$this->checker->isGranted($permission)) {
                throw new AccessDeniedException();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => ['onControllerArgument']];
    }
}
