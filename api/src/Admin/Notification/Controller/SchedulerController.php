<?php

namespace App\Admin\Notification\Controller;

use App\Admin\Notification\Dto\SchedulerDto;
use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\SchedulerPermission;
use App\Admin\Notification\Repository\SchedulerRepository;
use App\Admin\Notification\Resource\SchedulerResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SchedulerController extends AbstractApiController
{
    public function __construct(private readonly SchedulerRepository $repo)
    {
    }

    #[Thor(
        group: 'Scheduled Notifications',
        desc: 'List Scheduled Notifications',
        response: [200 => ['data' => SchedulerResource::class]],
        paginate: true,
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
        group: 'Scheduled Notifications',
        desc: 'Create Scheduled Notification',
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
        group: 'Scheduled Notifications',
        desc: 'Edit Scheduled Notification',
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
        group: 'Scheduled Notifications',
        desc: 'Delete Scheduled Notification',
        order: 3
    )]
    #[Route(path: '/v1/admin/scheduler/{id}', requirements: ['id' => Requirement::ULID], methods: ['DELETE'])]
    #[IsGranted(SchedulerPermission::ROLE_SCHEDULER_DELETE->value)]
    public function delete(Scheduler $scheduledNotification): ApiResponse
    {
        $this->repo->delete($scheduledNotification);

        return ApiResponse::create()->addMessage('Successfully deleted');
    }
}
