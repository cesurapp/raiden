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
class OtpKeyRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtpKey::class);
    }

    /**
     * Create OTP Key.
     */
    public function create(User $user, OtpType $type): OtpKey
    {
        $otp = (new OtpKey())
            ->setOwner($user)
            ->setType($type)
            ->setExpiredAt(new \DateTimeImmutable('+3 minute'))
            ->setKey(random_int(100000, 999999));

        $this->add($otp);

        return $otp;
    }

    /**
     * Check OTP Key is Valid.
     */
    public function check(User $user, OtpType $type, string $key): bool
    {
        /** @var OtpKey|null $otp */
        $otp = $this->createQueryBuilder('o')
            ->where('o.key = :key')
            ->andWhere('IDENTITY(o.owner) = :owner')
            ->andWhere('o.type = :type')
            ->andWhere('o.expiredAt >= :expired')
            ->andWhere('o.used = :used')
            ->setParameters([
                'key' => $key,
                'owner' => $user->getId()->toRfc4122(),
                'type' => $type->value,
                'expired' => new \DateTimeImmutable(),
                'used' => false,
            ])
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($otp) {
            $otp->setUsed(true);
            $this->add($otp);

            return true;
        }

        return false;
    }
}
