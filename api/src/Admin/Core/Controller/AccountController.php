<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Dto\ProfileDto;
use App\Admin\Core\Dto\UserDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\AccountPermission;
use App\Admin\Core\Permission\UserType;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use App\Admin\Core\Service\PermissionManager;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends ApiController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $userRepo
    ) {
    }

    #[Thor(
        stack: 'Account Management|10',
        title: 'View Profile',
        response: [200 => ['data' => UserResource::class]],
        order: 1,
    )]
    #[Route(path: '/v1/admin/account/profile', methods: ['GET'])]
    public function showProfile(#[CurrentUser] User $user): ApiResponse
    {
        return $this->show($user);
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Edit Profile',
        response: [200 => ['data' => UserResource::class]],
        dto: ProfileDto::class,
        order: 2
    )]
    #[Route(path: '/v1/admin/account/profile', methods: ['PUT'])]
    public function editProfile(#[CurrentUser] User $user, ProfileDto $dto): ApiResponse
    {
        $user = $dto->initObject($user);
        if ($dto->validated('password')) {
            $user->setPassword($dto->validated('password'), $this->hasher);
        }
        $this->userRepo->add($user);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class)
            ->addMessage('Changes are saved');
    }

    #[Thor(
        stack: 'Account Management',
        title: 'List Accounts',
        response: [200 => ['data' => UserResource::class]],
        isPaginate: true,
        order: 3
    )]
    #[Route(path: '/v1/admin/account/manager', methods: ['GET'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_LIST->value)]
    public function list(UserRepository $userRepo): ApiResponse
    {
        $query = $userRepo->createQueryBuilder('q');

        return ApiResponse::create()
            ->setQuery($query)
            ->setPaginate()
            ->setResource(UserResource::class);
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Create Account',
        response: [200 => ['data' => UserResource::class]],
        dto: UserDto::class,
        order: 4
    )]
    #[Route(path: '/v1/admin/account/manager', methods: ['POST'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_CREATE->value)]
    public function create(UserDto $dto): ApiResponse
    {
        $dto->validate();

        if ($dto->validated('type') === UserType::SUPERADMIN->value) {
            $this->isGrantedDeny(UserType::SUPERADMIN->value);
        }

        // Init & Save
        $user = $dto->initObject(new User())->setPassword($dto->validated('password'), $this->hasher);
        $this->userRepo->add($user);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class)
            ->addMessage('Operation successful');
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Edit Account',
        response: [200 => ['data' => UserResource::class]],
        dto: UserDto::class,
        order: 5
    )]
    #[Route(path: '/v1/admin/account/manager/{id}', methods: ['PUT'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_EDIT->value)]
    public function edit(User $user, UserDto $dto): ApiResponse
    {
        $dto->setProp('id', $user->getId())->validate();

        if ($user->hasType(UserType::SUPERADMIN)) {
            $this->isGrantedDeny(UserType::SUPERADMIN->value);
        }

        $user = $dto->initObject($user);
        if ($dto->validated('password')) {
            $user->setPassword($dto->validated('password'), $this->hasher);
        }
        $this->userRepo->add($user);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class)
            ->addMessage('Changes are saved');
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Show Account',
        response: [200 => ['data' => UserResource::class]],
        order: 6,
    )]
    #[Route(path: '/v1/admin/account/manager/{id}', methods: ['GET'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_LIST->value)]
    public function show(User $user): ApiResponse
    {
        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class);
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Delete Account',
        order: 7
    )]
    #[Route(path: '/v1/admin/account/manager/{id}', methods: ['DELETE'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_DELETE->value)]
    public function delete(User $user, UserRepository $userRepo): ApiResponse
    {
        if ($user->hasType(UserType::SUPERADMIN)) {
            $this->isGrantedDeny(UserType::SUPERADMIN->value);
        }

        // Remove
        $userRepo->remove($user);

        return ApiResponse::create()->addMessage('Successfully deleted');
    }

    #[Thor(
        stack: 'Account Management',
        title: 'Edit Permission',
        request: [
            'permissions' => 'array',
        ],
        order: 10
    )]
    #[Route(path: '/v1/admin/account/permission/{id}', methods: ['PUT'])]
    #[IsGranted(AccountPermission::ROLE_ACCOUNT_PERMISSION->value)]
    public function editPermission(User $user, Request $request, PermissionManager $permissionManager): ApiResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Forbid User
        if ($user->hasType(UserType::USER)) {
            throw $this->createAccessDeniedException();
        }

        // Forbid SuperUser
        if ($user->hasType(UserType::SUPERADMIN)) {
            $this->isGrantedDeny(UserType::SUPERADMIN->value);
        }

        if (!$request->request->has('permissions')) {
            throw $this->createNotFoundException('Permissions not found!');
        }

        // Merge
        $permissions = array_intersect(
            $permissionManager->getPermissionsFlatten($user->getType()),
            $request->get('permissions')
        );
        if (!$currentUser->hasType(UserType::SUPERADMIN)) {
            $permissions = array_intersect($currentUser->getRoles(), $permissions);
        }

        // Init Permissions
        $user->setRoles($permissionManager->getPermissionToEnum($permissions));
        $this->userRepo->add($user);

        return ApiResponse::create()->addMessage('Operation successful');
    }
}
