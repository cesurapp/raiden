<?php

namespace App\Admin\Core\Repository;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use Doctrine\Persistence\ManagerRegistry;

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
    public function create(User $user, OtpType $type, int $expiredMinute = 3, ?string $address = null, ?string $phoneCountry = null): OtpKey
    {
        $otp = (new OtpKey())
            ->setOwner($user)
            ->setType($type)
            ->setAddress($address)
            ->setPhoneCountry($phoneCountry)
            ->setExpiredAt(new \DateTimeImmutable("+$expiredMinute minute"))
            ->setOtpKey(random_int(100000, 999999));

        $this->add($otp);

        return $otp;
    }

    /**
     * Check OTP Key is Valid.
     */
    public function check(User $user, OtpType|array $type, int $key, ?string $address = null): ?OtpKey
    {
        $qb = $this->createQueryBuilder('q')
            ->andWhere('q.otpKey = :key')
            ->andWhere('q.owner = :owner')
            ->andWhere('q.type IN(:type)')
            ->andWhere('q.expiredAt >= :expired')
            ->andWhere('q.used = :used')
            ->setParameters([
                'key' => $key,
                'type' => is_array($type) ? $type : [$type],
                'expired' => new \DateTimeImmutable(),
                'used' => false,
            ])
            ->setParameter('owner', $user->getId(), 'ulid');

        if ($address) {
            $qb->andWhere('q.address = :address')->setParameter('address', $address);
        }

        /** @var OtpKey|null $otp */
        $otp = $qb->getQuery()->getOneOrNullResult();
        if (!$otp) {
            return null;
        }

        $this->add($otp->setUsed(true));

        return $otp;
    }

    /**
     * Disable Same Type Codes.
     */
    public function disableOtherCodes(OtpKey $otpKey): void
    {
        $this->createQueryBuilder('q')
            ->andWhere('q.owner = :owner')
            ->andWhere('q.type = :type')
            ->setParameter('type', $otpKey->getType())
            ->setParameter('owner', $otpKey->getOwner()->getId(), 'ulid')
            ->set('q.used', 'true')
            ->update()->getQuery()->execute();
    }

    /**
     * Clear All Expired OTP Keys.
     */
    public function clearExpired(): void
    {
        $this->createQueryBuilder('q')
            ->where('q.expiredAt <= :expire')
            ->setParameter('expire', new \DateTimeImmutable('-120 minute'))
            ->delete()->getQuery()->execute();
    }

    /**
     * Get Active OTP Key.
     */
    public function getActiveKey(User $user, OtpType $type): OtpKey
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.type = :type')
            ->andWhere('q.used = :used')
            ->andWhere('q.owner = :owner')
            ->andWhere('q.expiredAt >= :expired')
            ->setParameter('type', $type)
            ->setParameter('used', false)
            ->setParameter('owner', $user->getId(), 'ulid')
            ->setParameter('expired', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
