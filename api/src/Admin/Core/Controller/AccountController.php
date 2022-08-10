<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Resource\UserResource;
use App\Admin\Notification\Enum\NotificationType;
use App\Admin\Notification\Service\NotificationPusher;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractApiController
{
    #[Thor(group: 'Profile', desc: 'Profile Details')]
    #[Route(path: '/v1/profile')]
    public function getProfile(#[CurrentUser] ?User $user): ApiResponse
    {
        return ApiResponse::create()->addMessage('asdasd');
    }

    #[Thor(group: 'Profile', desc: 'Admin Profile Details')]
    #[Route(path: '/v1/admin/profile')]
    public function me(#[CurrentUser] ?User $user, NotificationPusher $pusher): ApiResponse
    {
        $pusher->send(
            $pusher->create('Dowas asdasds asd assada', 'asdsad asd asdas ashd askd haskd', NotificationType::DANGER)
            ->addDownloadAction('https://facebook.com')
        );

        return ApiResponse::create()
            ->setData([$user])
            ->setResource(UserResource::class);
    }

    public function showProfile()
    {
    }

    public function updateProfile()
    {
    }

    public function showOrganization()
    {
    }

    public function updateOrganization()
    {
    }

    public function updatePassword()
    {
    }
}
