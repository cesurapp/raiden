<?php

namespace App\Admin\Core\Security;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Admin\Core\Exception\TokenExpiredException;
use App\Admin\Core\Repository\UserRepository;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private UserRepository $userRepo, private JWT $jwt)
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        return new UserBadge($accessToken, fn (string $token) => $this->userRepo->find($this->decodeJWT($token)['id']));
    }

    private function decodeJWT(string $token): array
    {
        try {
            return $this->jwt->decode($token);
        } catch (JWTException $exception) {
            if (JWT::ERROR_TOKEN_EXPIRED === $exception->getCode()) {
                throw new TokenExpiredException();
            }

            throw $exception;
        }
    }
}
