<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractApiController
{
    #[Route(path: '/v1/profile')]
    public function getProfile(#[CurrentUser] ?User $user): ApiResponse
    {
        return ApiResponse::create()->setData(['asdas']);
    }

    #[Route(path: '/v1/admin/profile')]
    public function me(#[CurrentUser] ?User $user): ApiResponse
    {
        return ApiResponse::create()
            ->setData([$user])
            ->setResource(UserResource::class);
    }
}
