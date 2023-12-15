<?php

namespace App\Admin\Core\Repository;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ApiServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->add($user);
    }

    /**
     * Login User Methods.
     */
    public function loadUserByIdentifier(string|int $identifier): ?User
    {
        $q = $this->createQueryBuilder('q');

        if (is_numeric($identifier)) {
            $q->where('q.phone = :identity')->setParameter('identity', (int) $identifier);
        } else {
            $q->where('q.email = :identity')->setParameter('identity', $identifier);
        }

        return $q->getQuery()->getOneOrNullResult();
    }

    /**
     * Approve User.
     */
    public function approve(User $user, OtpKey $otpKey): void
    {
        if (OtpType::EMAIL === $otpKey->getType()) {
            $user->setEmailApproved(true);
        }

        if (OtpType::PHONE === $otpKey->getType()) {
            $user->setPhoneApproved(true);
        }

        $this->add($user);
    }
}
