<?php

namespace App\Admin\Notification\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Notification\Dto\FcmRegisterDto;
use App\Admin\Notification\Repository\DeviceRepository;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Firebase Cloud Messaging Device Controller.
 */
class DeviceController extends AbstractApiController
{
    #[Thor(
        group: 'Notification',
        desc: 'Register Device to Firebase Cloud Messaging',
        dto: FcmRegisterDto::class,
        order: 10
    )]
    #[Route(path: '/v1/main/notification/fcm-register', methods: ['POST'])]
    public function register(#[CurrentUser] User $user, FcmRegisterDto $dto, DeviceRepository $repo): ApiResponse
    {
        $repo->register($dto, $user);

        return ApiResponse::create()->addMessage('Operation successful.');
    }
}
