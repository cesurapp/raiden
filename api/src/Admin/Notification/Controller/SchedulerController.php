<?php

namespace App\Admin\Notification\Controller;

use App\Admin\Notification\Dto\SchedulerDto;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Permission\SchedulerPermission;
use App\Admin\Notification\Repository\SchedulerRepository;
use App\Admin\Notification\Resource\SchedulerResource;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SchedulerController extends ApiController
{
    public function __construct(private readonly SchedulerRepository $repo)
    {
    }

    #[Thor(
        stack: 'Scheduled Notifications',
        title: 'List Scheduled Notifications',
        response: [200 => ['data' => SchedulerResource::class]],
        isPaginate: true,
        order: 0
    )]
    #[Route(path: '/v1/admin/scheduler', methods: ['GET'])]
    #[IsGranted(SchedulerPermission::ROLE_SCHEDULER_LIST->value)]
    public function list(): ApiResponse
    {
        return ApiResponse::create()
            ->setResource(SchedulerResource::class)
            ->setQuery($this->repo->list())
            ->setPaginate();
    }

    #[Thor(
        stack: 'Scheduled Notifications',
        title: 'Create Scheduled Notification',
        dto: SchedulerDto::class,
        order: 1
    )]
    #[Route(path: '/v1/admin/scheduler', methods: ['POST'])]
    #[IsGranted(SchedulerPermission::ROLE_SCHEDULER_CREATE->value)]
    public function create(SchedulerDto $dto): ApiResponse
    {
        $sn = $dto->initObject(new Scheduler());
        $this->repo->add($sn);

        return ApiResponse::create()
            ->setData($sn)
            ->setResource(SchedulerResource::class)
            ->addMessage('Operation successful');
    }

    #[Thor(
        stack: 'Scheduled Notifications',
        title: 'Edit Scheduled Notification',
        dto: SchedulerDto::class,
        order: 2
    )]
    #[Route(path: '/v1/admin/scheduler/{id}', methods: ['PUT'])]
    #[IsGranted(SchedulerPermission::ROLE_SCHEDULER_EDIT->value)]
    public function edit(Scheduler $sn, SchedulerDto $dto): ApiResponse
    {
        $this->repo->add($dto->initObject($sn));

        return ApiResponse::create()
            ->setData($sn)
            ->setResource(SchedulerResource::class)
            ->addMessage('Changes are saved');
    }

    #[Thor(
        stack: 'Scheduled Notifications',
        title: 'Delete Scheduled Notification',
        order: 3
    )]
    #[Route(path: '/v1/admin/scheduler/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['DELETE'])]
    #[IsGranted(SchedulerPermission::ROLE_SCHEDULER_DELETE->value)]
    public function delete(Scheduler $scheduledNotification): ApiResponse
    {
        $this->repo->delete($scheduledNotification);

        return ApiResponse::create()->addMessage('Successfully deleted');
    }
}
