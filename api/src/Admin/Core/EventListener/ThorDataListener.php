<?php

namespace App\Admin\Core\EventListener;

use App\Admin\Core\Permission\UserType;
use App\Admin\Core\Service\PermissionManager;
use Cesurapp\ApiBundle\Thor\Event\ThorDataEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ThorDataEvent::class, method: 'onData')]
readonly class ThorDataListener
{
    public function __construct(private PermissionManager $permissionManager)
    {
    }

    public function onData(ThorDataEvent $event): void
    {
        $event->data['_enums']['UserType'] = UserType::class;
        $event->data['_enums']['Permission'] = $this->permissionManager->getPermissionsValues();
    }
}
