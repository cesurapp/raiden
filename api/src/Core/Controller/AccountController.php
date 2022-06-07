<?php

namespace App\Core\Controller;

use App\Core\Entity\User;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractApiController
{
    #[Route(path: 'profile')]
    public function getProfile(#[CurrentUser] ?User $user): ApiResponse
    {
        return ApiResponse::create()
            ->setData(['asdas']);
    }
}
