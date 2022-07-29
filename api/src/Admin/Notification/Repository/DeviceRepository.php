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

    /**
     * Register FCM Device.
     */
    public function register(FcmRegisterDto $dto, User $user): void
    {
        $device = (new Device())
            ->setToken($dto->validated('token'))
            ->setType(DeviceType::from($dto->validated('device')))
            ->setOwner($user);

        $this->add($device);
    }

    /**
     * Get Devices.
     *
     * @return Device[]|null
     */
    public function getDevices(User $user): ?array
    {
        return $this->createQueryBuilder('d')
            ->where('IDENTITY(d.owner) = :owner')
            ->setParameter('owner', $user->getId(), 'ulid')
            ->getQuery()->getResult();
    }

    /**
     * Remove Device DQL.
     */
    public function removeDevice(string $deviceId): void
    {
        $this->remove($this->em()->getReference(Device::class, $deviceId));
    }
}
