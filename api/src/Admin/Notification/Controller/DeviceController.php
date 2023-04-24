<?php

namespace App\Admin\Notification\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Notification\Dto\FcmRegisterDto;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Enum\DevicePermission;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Resource\DeviceResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        // Check
        if (!$repo->check($dto->validated('token'), $dto->validated('device'))) {
            $repo->register($dto, $user);
        }

        return ApiResponse::create()->addMessage('Operation successful');
    }

    #[Thor(
        group: 'Notification Devices',
        desc: 'List Devices',
        response: [200 => ['data' => DeviceResource::class]],
        paginate: true,
        order: 1
    )]
    #[Route(path: '/v1/admin/notification/device', methods: ['GET'])]
    #[IsGranted(DevicePermission::ROLE_DEVICE_LIST->value)]
    public function list(DeviceRepository $repo): ApiResponse
    {
        $query = $repo->createQueryBuilder('q');

        return ApiResponse::create()
            ->setQuery($query)
            ->setPaginate()
            ->setResource(DeviceResource::class);
    }

    #[Thor(
        group: 'Notification Devices',
        desc: 'Delete Device',
        order: 3
    )]
    #[Route(path: '/v1/admin/notification/device/{id}', methods: ['DELETE'])]
    #[IsGranted(DevicePermission::ROLE_DEVICE_DELETE->value)]
    public function delete(Device $device, DeviceRepository $repo): ApiResponse
    {
        $repo->remove($device);

        return ApiResponse::create()->addMessage('Operation successful');
    }
}
