<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Attribute\IsGranted;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractApiController
{
    #[Thor(group: 'Account Management|10', desc: 'View Profile', order: 1)]
    #[Route(path: '/v1/admin/account/profile', methods: ['GET'])]
    public function showProfile(#[CurrentUser] User $user): ApiResponse
    {
        return ApiResponse::create()->setData($user)->setResource(UserResource::class);
    }

    #[Thor(group: 'Account Management', desc: 'Edit Profile', order: 2)]
    #[Route(path: '/v1/admin/account/profile', methods: ['POST'])]
    public function editProfile(#[CurrentUser] User $user): ApiResponse
    {
        return ApiResponse::create()->setData([])->setResource(UserResource::class);
    }

    #[Thor(group: 'Account Management', desc: 'Change Password', order: 3)]
    #[Route(path: '/v1/admin/account/password', methods: ['POST'])]
    public function editPassword(#[CurrentUser] User $user): ApiResponse
    {
    }









    #[Thor(group: 'Account Management', desc: 'List Accounts', order: 4)]
    #[Route(path: '/v1/admin/account/list', methods: ['GET'])]
    #[IsGranted(['ROLE_ADMIN'])]
    public function listAccount(Request $request): ApiResponse
    {
        return ApiResponse::create()->setData([]);
    }

    #[Thor(group: 'Account Management', desc: 'Create Account', order: 5)]
    #[Route(path: '/v1/admin/account/create', methods: ['GET'])]
    #[IsGranted(['ROLE_TEST'])]
    public function createAccount(): ApiResponse
    {
        return ApiResponse::create()->setData([]);
    }

    #[Thor(group: 'Account Management', desc: 'Show Account', order: 6)]
    #[Route(path: '/v1/admin/account/show/{id}', methods: ['GET'])]
    public function showAccount(User $user): ApiResponse
    {
    }

    #[Thor(group: 'Account Management', desc: 'Change Password', order: 7)]
    #[Route(path: '/v1/admin/account/edit/{id}', methods: ['POST'])]
    public function editAccount(User $user): ApiResponse
    {
    }

    #[Thor(group: 'Account Management', desc: 'Delete Account', order: 8)]
    #[Route(path: '/v1/admin/account/delete/{id}', methods: ['delete'])]
    public function deleteAccount(User $user): ApiResponse
    {
    }

    #[Thor(group: 'Account Management', desc: 'View Permission', order: 9)]
    #[Route(path: '/v1/admin/account/permission/{id}', methods: ['GET'])]
    public function showPermission(User $user): ApiResponse
    {
    }

    #[Thor(group: 'Account Management', desc: 'Edit Permission', order: 10)]
    #[Route(path: '/v1/admin/account/permission/{id}', methods: ['POST'])]
    public function editPermission(): ApiResponse
    {
    }
}
