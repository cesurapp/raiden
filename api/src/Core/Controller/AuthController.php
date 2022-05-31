<?php

namespace App\Core\Controller;

use App\Core\Entity\UserEntity;
use App\Core\Request\Login;
use App\Core\Resources\FailedTaskResource;
use App\Core\Resources\UserResource;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Package\StorageBundle\Storage\Storage;
use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route(path: '/')]
    #[Thor(group: 'Homepage', desc: 'View Home Page')]
    public function home(Request $request, Storage $storage): ApiResponse
    {
        dump($request->files->count());
        dump($request->files->get('filesss'));
        // foreach (['', '', '', '', '', '', ''] as $item) {
        //    TaskHandler::dispatch(TestTask::class, []);
        // }

        return ApiResponse::create()
            ->setData(['tama']);
    }

    #[Route(path: '/user', methods: ['GET'])]
    #[Thor(desc: 'User List', query: [
        'name' => '?string',
        'fullName' => '?string',
        'filter' => [
            'id' => '?int',
            'name' => '?string',
            'fullName' => '?string',
        ],
        'data' => '?array',
        'data2' => [],
    ], paginate: true)]
    public function list(FailedTaskRepository $userRepo): ApiResponse
    {
        $data = $userRepo->createQueryBuilder('q')->where('q.id = :p')->setParameter('p', '017f83b0-5d94-2b32-88a7-044906290a2f')->getQuery()->getResult();

        return ApiResponse::create()
            ->setData($data)
            ->setResource(FailedTaskResource::class);
        // ->setPaginate(1, false);
    }

    #[Route(path: '/user/{id}', methods: ['GET'])]
    #[Thor(response: [UserResource::class])]
    public function show(int|null $user, Request $request): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(UserResource::class)
            ->setData($user);
    }

    #[Route(path: '/user/{id}', methods: ['PUT'])]
    #[Thor(response: [UserResource::class], dto: Login::class)]
    public function edit(int $id, Login $login): ApiResponse
    {
        dump($login->validated());

        return ApiResponse::create()
            ->setData([]);
        // ->setResource(UserResource::class)
            // ->setData($user);
    }

    #[Route(path: '/user/{id}/{status}', methods: ['DELETE'])]
    public function delete(UserEntity $user, string $status): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(UserResource::class)
            ->setData($user);
    }
}
