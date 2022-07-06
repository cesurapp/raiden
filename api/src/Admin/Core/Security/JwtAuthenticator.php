<?php

namespace App\Admin\Core\Security;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Admin\Core\Exception\TokenExpiredException;
use App\Admin\Core\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UserRepository $userRepo, private JWT $jwt)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        if (!$apiToken = $request->headers->get('Authorization')) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        return new SelfValidatingPassport(new UserBadge($apiToken, function (string $token) {
            return $this->userRepo->find($this->decodeJWT($token)['id']);
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }

    private function decodeJWT(string $bearer): array
    {
        $token = explode(' ', $bearer);
        if (!isset($token[1])) {
            throw new JWTException('Authorization token format incorrect.');
        }

        try {
            return $this->jwt->decode($token[1]);
        } catch (JWTException $exception) {
            if (JWT::ERROR_TOKEN_EXPIRED === $exception->getCode()) {
                throw new TokenExpiredException();
            }

            throw $exception;
        }
    }
}
