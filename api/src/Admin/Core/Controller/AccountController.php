<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Dto\UserDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Attribute\IsGranted;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractApiController
{
    #[Thor(
        group: 'Account Management|10',
        desc: 'View Profile',
        response: [
            200 => UserResource::class,
        ],
        order: 1,
    )]
    #[Route(path: '/v1/admin/account/profile', methods: ['GET'])]
    public function showProfile(#[CurrentUser] User $user): ApiResponse
    {
        return $this->showAccount($user);
    }

    #[Thor(group: 'Account Management', desc: 'Edit Profile', order: 2)]
    #[Route(path: '/v1/admin/account/profile', methods: ['POST'])]
    public function editProfile(#[CurrentUser] User $user): ApiResponse
    {
        return $this->editAccount($user);
    }

    #[Thor(group: 'Account Management', desc: 'Change Password', order: 3)]
    #[Route(path: '/v1/admin/account/password', methods: ['POST'])]
    public function editPassword(#[CurrentUser] User $user): ApiResponse
    {
        return ApiResponse::create()->setData([]);
    }

    #[Thor(
        group: 'Account Management',
        desc: 'List Accounts',
        response: [
            200 => [UserResource::class],
        ],
        paginate: true,
        order: 4
    )]
    #[Route(path: '/v1/admin/account/list', methods: ['GET'])]
    #[IsGranted(roles: ['ROLE_ACCOUNT_LIST'])]
    public function listAccount(UserRepository $userRepo): ApiResponse
    {
        return ApiResponse::create()
            ->setData([])
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Account Management',
        desc: 'Show Account',
        response: [
            200 => UserResource::class,
        ],
        order: 6,
    )]
    #[Route(path: '/v1/admin/account/show/{id}', methods: ['GET'])]
    #[IsGranted(['ROLE_ACCOUNT_LIST'])]
    public function showAccount(User $user): ApiResponse
    {
        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Account Management',
        desc: 'Create Account',
        response: [
            200 => UserResource::class,
        ],
        dto: UserDto::class,
        order: 5
    )]
    #[Route(path: '/v1/admin/account/create', methods: ['POST'])]
    #[IsGranted(['ROLE_ACCOUNT_CREATE'])]
    public function createAccount(UserDto $dto, UserPasswordHasherInterface $hasher, UserRepository $userRepo): ApiResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Init & Save
        $user = $dto->initObject(new User())
            ->setOrganization($currentUser->getOrganization())
            ->setPassword($dto->validated('password'), $hasher);
        $userRepo->add($user);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Account Management',
        desc: 'Change Password',
        response: [
            200 => UserResource::class,
        ],
        dto: UserDto::class,
        order: 7
    )]
    #[Route(path: '/v1/admin/account/edit/{id}', methods: ['POST'])]
    #[IsGranted(['ROLE_ACCOUNT_EDIT'])]
    public function editAccount(User $user, UserDto $dto, UserPasswordHasherInterface $hasher, UserRepository $userRepo): ApiResponse
    {
        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Account Management',
        desc: 'Delete Account',
        order: 8
    )]
    #[Route(path: '/v1/admin/account/delete/{id}', methods: ['delete'])]
    #[IsGranted(['ROLE_ACCOUNT_DELETE'])]
    public function deleteAccount(User $user, UserRepository $userRepo): ApiResponse
    {
        // Remove
        $userRepo->remove($user);

        return ApiResponse::create()->addMessage('User deleted');
    }

    #[Thor(
        group: 'Account Management',
        desc: 'View Permission',
        order: 9
    )]
    #[Route(path: '/v1/admin/account/permission/{id}', methods: ['GET'])]
    #[IsGranted(['ROLE_ACCOUNT_PERMISSON'])]
    public function showPermission(User $user): ApiResponse
    {
        return ApiResponse::create()->addMessage('User deleted');

    }

    #[Thor(
        group: 'Account Management',
        desc: 'Edit Permission',
        order: 10
    )]
    #[Route(path: '/v1/admin/account/permission/{id}', methods: ['POST'])]
    #[IsGranted(['ROLE_ACCOUNT_PERMISSON'])]
    public function editPermission(): ApiResponse
    {
        return ApiResponse::create()->addMessage('User deleted');

    }
}
