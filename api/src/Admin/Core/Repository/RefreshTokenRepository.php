<?php

namespace App\Admin\Core\Repository;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\RefreshToken;
use App\Admin\Core\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends ApiServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ParameterBagInterface $bag)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * Clear All Expired Refresh Token.
     */
    public function clearExpiredToken(): void
    {
        $this->createQueryBuilder('q')
            ->where('q.expiredAt <= :expire')
            ->setParameter('expire', new \DateTimeImmutable())
            ->delete()->getQuery()->execute();
    }

    /**
     * Clear Token.
     */
    public function removeToken(string $token): void
    {
        if ($t = $this->findOneBy(['token' => $token])) {
            $this->remove($t);
        }
    }

    /**
     * Clear All Token.
     */
    public function clearAllToken(User $user): void
    {
        $this->createQueryBuilder('q')
            ->andWhere('IDENTITY(q.owner) = :ulid')
            ->setParameter('ulid', $user->getId(), 'ulid')
            ->delete()->getQuery()->execute();
    }

    /**
     * Create Refresh Token.
     */
    public function createToken(User $user, JWT $jwt, bool $clearOldToken = false): RefreshToken
    {
        if ($clearOldToken) {
            $this->clearAllToken($user);
        }

        $exp = time() + (86400 * $this->bag->get('core.refresh_token_exp'));

        $token = (new RefreshToken())
            ->setToken($jwt->encode([
                'id' => $user->getId()->toBase32(),
                'exp' => $exp,
            ]))
            ->setOwner($user)
            ->setExpiredAt(\DateTimeImmutable::createFromFormat('U', (string) $exp));

        $this->em()->persist($token);
        $this->em()->flush();

        return $token;
    }

    /**
     * Check RefreshToken is Valid.
     */
    public function checkToken(string $token, int $expiredTimeStamp): bool
    {
        return (bool) $this->createQueryBuilder('q')
            ->where('q.token = :token')
            ->andWhere('q.expiredAt <= :expiredAt')
            ->setParameter('token', $token)
            ->setParameter('expiredAt', \DateTimeImmutable::createFromFormat('U', (string) $expiredTimeStamp))
            ->getQuery()->getOneOrNullResult();
    }
}
