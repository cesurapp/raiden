<?php

namespace App\Core\Repository;

use Ahc\Jwt\JWT;
use App\Core\Entity\RefreshToken;
use App\Core\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, private ParameterBagInterface $bag)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * Clear All Token.
     */
    public function clearToken(User $user, ?string $excludeToken = null): void
    {
        $this->createQueryBuilder('t')
            ->where('t.token <> :token')
            ->andWhere('IDENTITY(t.owner) = :ulid')
            ->setParameter('token', $excludeToken)
            ->setParameter('ulid', $user->getId(), 'ulid')
            ->delete()->getQuery()->execute();
    }

    /**
     * Create Refresh Token.
     */
    public function createToken(User $user, JWT $jwt, bool $clearOldToken = true): RefreshToken
    {
        if ($clearOldToken) {
            $this->clearToken($user);
        }

        $token = (new RefreshToken())
            ->setToken($jwt->encode(['exp' => time() + (86400 * $this->bag->get('core.refresh_token_exp'))]))
            ->setOwner($user);

        $this->em()->persist($token);
        $this->em()->flush();

        return $token;
    }
}
