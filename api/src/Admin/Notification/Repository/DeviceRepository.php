<?php

namespace App\Admin\Notification\Repository;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Repository\ApiServiceEntityRepository;
use App\Admin\Notification\Dto\FcmRegisterDto;
use App\Admin\Notification\Entity\Device;
use App\Admin\Notification\Enum\DeviceType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends ApiServiceEntityRepository
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
        return $this->createQueryBuilder('q')
            ->where('IDENTITY(q.owner) = :owner')
            ->setParameter('owner', $user->getId(), 'uuid')
            ->getQuery()->getResult();
    }

    /**
     * Remove Device DQL.
     */
    public function removeDevice(Uuid|string $deviceId): void
    {
        if (is_string($deviceId)) {
            $deviceId = Uuid::fromString($deviceId);
        }

        $this->remove($this->em()->getReference(Device::class, $deviceId));
    }

    public function check(string $token, string $device): bool
    {
        return (bool) $this->count([
            'token' => $token,
            'type' => $device,
        ]);
    }
}
