<?php

namespace App\Admin\Core\Repository;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use Doctrine\Persistence\ManagerRegistry;
use Package\ApiBundle\Repository\ApiServiceEntityRepository;

/**
 * @method OtpKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method OtpKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method OtpKey[]    findAll()
 * @method OtpKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtpKeyRepository extends ApiServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtpKey::class);
    }

    /**
     * Create OTP Key.
     */
    public function create(User $user, OtpType $type, int $expiredMinute = 3): OtpKey
    {
        $otp = (new OtpKey())
            ->setOwner($user)
            ->setType($type)
            ->setExpiredAt(new \DateTimeImmutable("+$expiredMinute minute"))
            ->setOtpKey(random_int(100000, 999999));

        $this->add($otp);

        return $otp;
    }

    /**
     * Check OTP Key is Valid.
     */
    public function check(User $user, OtpType|array $type, int $key): ?OtpKey
    {
        /** @var OtpKey|null $otp */
        $otp = $this->createQueryBuilder('o')
            ->andWhere('o.otpKey = :key')
            ->andWhere('o.owner = :owner')
            ->andWhere('o.type IN(:type)')
            ->andWhere('o.expiredAt >= :expired')
            ->andWhere('o.used = :used')
            ->setParameters([
                'key' => $key,
                'type' => is_array($type) ? $type : [$type],
                'expired' => new \DateTimeImmutable(),
                'used' => false,
            ])
            ->setParameter('owner', $user->getId(), 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($otp) {
            $otp->setUsed(true);
            $this->add($otp);

            return $otp;
        }

        return null;
    }

    /**
     * Disable Same Type Codes.
     */
    public function disableOtherCodes(OtpKey $otpKey): void
    {
        $this->createQueryBuilder('o')
            ->andWhere('o.owner = :owner')
            ->andWhere('o.type = :type')
            ->setParameter('type', $otpKey->getType())
            ->setParameter('owner', $otpKey->getOwner()->getId(), 'ulid')
            ->set('o.used', 'true')
            ->update()->getQuery()->execute();
    }

    /**
     * Clear All Expired OTP Keys.
     */
    public function clearExpired(): void
    {
        $this->createQueryBuilder('o')
            ->where('o.expiredAt <= :expire')
            ->setParameter('expire', new \DateTimeImmutable('-120 minute'))
            ->delete()->getQuery()->execute();
    }

    /**
     * Get Active OTP Key.
     */
    public function getActiveKey(User $user, OtpType $type): OtpKey
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.type = :type')
            ->andWhere('o.used = :used')
            ->andWhere('o.owner = :owner')
            ->andWhere('o.expiredAt >= :expired')
            ->setParameter('type', $type)
            ->setParameter('used', false)
            ->setParameter('owner', $user->getId(), 'ulid')
            ->setParameter('expired', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
