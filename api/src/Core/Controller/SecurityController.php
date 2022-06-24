<?php

namespace App\Core\Controller;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Core\Entity\User;
use App\Core\Exception\TokenExpiredException;
use App\Core\Exception\RefreshTokenExpiredException;
use App\Core\Repository\RefreshTokenRepository;
use App\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Global Authorization Management.
 */
class SecurityController extends AbstractApiController
{
    #[Thor(
        group: 'Security',
        desc: 'Login User',
        request: [
            'username' => 'string',
            'password' => 'string',
        ],
        response: [
            200 => [
                'user' => UserResource::class,
                'token' => 'string',
                'refresh_token' => 'string',
            ],
            BadCredentialsException::class,
            TokenExpiredException::class,
            RefreshTokenExpiredException::class,
        ],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        return ApiResponse::create()
            ->setData([
                'user' => $user,
                'token' => $jwt->encode(['id' => $user->getId()->toBase32()]),
                'refresh_token' => $repo->createToken($user, $jwt)->getToken(),
            ])
            ->setResource(UserResource::class);
    }

    #[Thor(group: 'Security', desc: 'Logout', requireAuth: false)]
    #[Route(path: '/v1/auth/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): ApiResponse
    {
        return ApiResponse::create()->addMessage('Operation successful.');
    }

    #[Thor(group: 'Security', desc: 'Login with Refresh Token', requireAuth: false)]
    #[Route(path: '/v1/auth/refresh-token', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        // Verify Refresh Token
        try {
            $token = $jwt->decode($request->get('token'));
            if (!$repo->checkToken($request->get('token'), $token['exp'])) {
                throw new RefreshTokenExpiredException();
            }
        } catch (JWTException $exception) {
            if (JWT::ERROR_TOKEN_EXPIRED === $exception->getCode()) {
                throw new RefreshTokenExpiredException();
            }

            throw $exception;
        }

        // Generate New Token
        return ApiResponse::create()->setData(['token' => $jwt->encode(['id' => $token['id']])]);
    }

    #[Thor(group: 'Security', desc: 'Register', requireAuth: false)]
    #[Route(path: '/v1/auth/register', name: 'api_register', methods: ['POST'])]
    public function register(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Thor(group: 'Security', desc: 'Account Confirmation', requireAuth: false)]
    #[Route(path: '/v1/auth/register-confirm/{token}', name: 'api_register_confirm', methods: ['GET'])]
    public function confirmation(string $token): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Thor(group: 'Security', desc: 'Reset Password Request', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-request', name: 'api_reset_request', methods: ['POST'])]
    public function resetRequest(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Thor(group: 'Security', desc: 'Change Password', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-password/{token}', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(string $token): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }
}
