<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Dto\ProfileDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;

class ProfileController extends ApiController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $userRepo,
    ) {
    }

    #[Thor(
        stack: 'Profile Management',
        title: 'View Profile',
        response: [200 => ['data' => UserResource::class]],
        order: 1,
    )]
    #[Route(path: '/v1/main/profile', methods: ['GET'])]
    public function show(#[CurrentUser] User $user): ApiResponse
    {
        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class);
    }

    #[Thor(
        stack: 'Profile Management',
        title: 'Edit Profile',
        response: [200 => ['data' => UserResource::class]],
        dto: ProfileDto::class,
        order: 2
    )]
    #[Route(path: '/v1/main/profile', methods: ['PUT'])]
    public function edit(#[CurrentUser] User $user, ProfileDto $dto): ApiResponse
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
}
