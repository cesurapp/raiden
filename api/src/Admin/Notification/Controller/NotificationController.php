<?php

namespace App\Admin\Notification\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\DeviceType;
use App\Admin\Notification\Repository\NotificationRepository;
use App\Admin\Notification\Resource\NotificationResource;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class NotificationController extends ApiController
{
    public function __construct(private readonly NotificationRepository $repo)
    {
    }

    #[Thor(
        stack: 'Notification',
        title: 'Get Unread Notification Count',
        response: [
            200 => [
                'data' => 'int',
            ],
        ],
        order: 1,
    )]
    #[Route(path: '/v1/main/notification/unread-count', methods: ['GET'])]
    public function unreadCount(#[CurrentUser] User $user): ApiResponse
    {
        return ApiResponse::create()->setData($this->repo->getUnreadCount($user));
    }

    #[Thor(
        stack: 'Notification|2',
        title: 'List Notification',
        info: 'Global Notification',
        response: [200 => ['data' => NotificationResource::class]],
        isPaginate: true,
        order: 0
    )]
    #[Route(path: '/v1/main/notification/{device}', methods: ['GET'])]
    public function list(#[CurrentUser] User $user, DeviceType $device): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(NotificationResource::class, $device->value)
            ->setQuery($this->repo->list($user))
            ->setPaginate(10);
    }

    #[Thor(
        stack: 'Notification',
        title: 'Read All Notification',
        order: 2
    )]
    #[Route(path: '/v1/main/notification/read-all', methods: ['POST'])]
    public function readAll(#[CurrentUser] User $user): ApiResponse
    {
        $this->repo->readAll($user);

        return ApiResponse::create()->addMessage('All notifications marked as read');
    }

    #[Thor(
        stack: 'Notification',
        title: 'Read Notification',
        order: 3,
    )]
    #[Route(path: '/v1/main/notification/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['PUT'])]
    public function read(#[CurrentUser] User $user, Notification $notification): ApiResponse
    {
        if ($user !== $notification->getOwner()) {
            throw $this->createAccessDeniedException();
        }

        $this->repo->read($notification);

        return ApiResponse::create()->addMessage('Operation successful');
    }

    #[Thor(
        stack: 'Notification',
        title: 'Delete Notification',
        order: 4
    )]
    #[Route(path: '/v1/main/notification/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, Notification $notification): ApiResponse
    {
        if ($user !== $notification->getOwner()) {
            throw $this->createAccessDeniedException();
        }

        $this->repo->delete($notification);

        return ApiResponse::create()->addMessage('The notification has been deleted');
    }
}
