<?php

namespace App\Admin\Core\Repository;

use App\Admin\Core\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends BaseRepository implements PasswordUpgraderInterface, UserLoaderInterface
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

        $this->add($user, true);
    }

    /**
     * Login User Methods.
     */
    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :identity')
            ->orWhere('u.phone = :identity')
            ->setParameter('identity', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Confirm or Approve User.
     */
    public function confirmUser(User $user): void
    {
        $user->setConfirmationToken(null)->setApproved(true);
        $this->add($user);
    }

    /**
     * Create Password Reset Token.
     */
    public function resetRequest(User $user): void
    {
        $user
            ->createResetToken()
            ->setPasswordRequestedAt(new \DateTimeImmutable());

        $this->add($user);
    }

    /**
     * Reset User Password.
     */
    public function resetPassword(User $user, string $password, UserPasswordHasherInterface $hasher): void
    {
        $user
            ->setResetToken(null)
            ->setPasswordRequestedAt(null)
            ->setPassword($password, $hasher);

        $this->add($user);
    }
}
