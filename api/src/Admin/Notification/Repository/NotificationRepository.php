<?php

namespace App\Admin\Notification\Repository;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Repository\BaseRepository;
use App\Admin\Notification\Entity\Notification;
use App\Admin\Notification\Enum\NotificationType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, private TranslatorInterface $translator)
    {
        parent::__construct($registry, Notification::class);
    }

    public function create(User $user, string $title, string $message): Notification
    {
        return (new Notification())
            ->setOwner($user)
            ->setRead(false)
            ->setType(NotificationType::INFO)
            ->setTitle($this->translator->trans($title))
            ->setMessage($this->translator->trans($message));
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
     * Read Single Notification.
     */
    public function read(Notification $notification, bool $read = true): void
    {
        $notification->setRead($read);
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
            ->set('n.read', 'true')
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
