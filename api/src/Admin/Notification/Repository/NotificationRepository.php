<?php

namespace App\Admin\Notification\Repository;

use App\Admin\Core\Entity\User;
use App\Admin\Notification\Entity\Notification;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Package\ApiBundle\Repository\ApiServiceEntityRepository;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ApiServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Get Listing Query.
     */
    public function list(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('n')
            ->andWhere('IDENTITY(n.owner) = :owner')
            ->setParameter('owner', $user->getId(), 'ulid')
            ->orderBy('n.id', 'DESC');
    }

    /**
     * Get Unreaded Count.
     */
    public function getUnreadCount(User $user): int
    {
        return $this->count([
           'owner' => $user,
           'readed' => false,
        ]);
    }

    /**
     * Read Single Notification.
     */
    public function read(Notification $notification, bool $read = true): void
    {
        $notification->setReaded($read);
        $this->add($notification);
    }

    /**
     * Read All Notifications.
     */
    public function readAll(User $user): void
    {
        $this->createQueryBuilder('n')
            ->andWhere('IDENTITY(n.owner) = :owner')
            ->setParameter('owner', $user->getId(), 'ulid')
            ->update()
            ->set('n.readed', 'true')
            ->getQuery()->execute();
    }

    /**
     * Remove Notification.
     */
    public function delete(Notification $notification): void
    {
        $this->remove($notification);
    }
}
