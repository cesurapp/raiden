<?php

namespace App\Core\Controller;

use App\Core\Entity\UserEntity;
use App\Core\Repository\UserEntityRepository;
use App\Core\Request\Login;
use App\Core\Resources\UserResource;
use Package\ApiBundle\Documentation\Api;
use Package\ApiBundle\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route(path: '/')]
    #[Api(group: 'Homepage', desc: 'View Home Page', success: [
        200 => [
            'sdsad' => 'asdsadasas',
        ],
    ])]
    public function home(): ApiResponse
    {
        return ApiResponse::create()
            ->setData(['tama']);
    }

    #[Route(path: '/user', methods: ['GET'])]
    #[Api(desc: 'User List', get: [
        'name' => '?string',
        'fullName' => '?string',
        'filter' => [
            'id' => '?int',
            'name' => '?string',
            'fullName' => '?string',
        ],
        'data' => '?array',
        'data2' => [],
    ], resource: UserResource::class, paginate: true)]
    public function list(UserEntityRepository $userRepo): ApiResponse
    {
        return ApiResponse::create()
            ->setQuery($userRepo->createQueryBuilder('q'))
            ->setResource(UserResource::class)
            ->setPaginate(10, false);
    }

    #[Route(path: '/user/{id}', methods: ['GET'])]
    public function show(UserEntity|int|null $user): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(UserResource::class)
            ->setData($user);
    }

    #[Route(path: '/user/{id}', methods: ['PUT'])]
    #[Api(dto: Login::class)]
    public function edit(UserEntity $user): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(UserResource::class)
            ->setData($user);
    }

    #[Route(path: '/user/{id}', methods: ['DELETE'])]
    public function delete(UserEntity $user): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(UserResource::class)
            ->setData($user);
    }
}
