<?php

namespace App\Admin\Notification\Repository;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Repository\BaseRepository;
use App\Admin\Notification\Dto\FcmRegisterDto;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Enum\DeviceType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function register(FcmRegisterDto $dto, User $user): void
    {
        $device = (new Device())
            ->setToken($dto->validated('token'))
            ->setType(DeviceType::from($dto->validated('device')))
            ->setOwner($user);

        $this->add($device);
    }
}
