<?php

namespace App\Core\Controller;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Core\Entity\User;
use App\Core\Exception\TokenExpiredException;
use App\Core\Repository\RefreshTokenRepository;
use App\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
                'refresh-token' => 'string',
            ],
        ],
        requireAuth: false
    )]
    #[Route(path: '/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        return ApiResponse::result()
            ->setData([
                'user' => $user,
                'token' => $jwt->encode(['id' => $user->getId()->toBase32()]),
                'refresh-token' => $repo->createToken($user, $jwt)->getToken(),
            ])
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Security',
        desc: 'Logout',
        response: [
            200 => [
                'Operation successful.',
            ],
        ],
        requireAuth: false
    )]
    #[Route(path: '/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): ApiResponse
    {
        return ApiResponse::msgSuccess()->setData('Operation successful.');
    }

    #[Thor(group: 'Security', desc: 'Login with Refresh Token', requireAuth: false)]
    #[Route(path: '/refresh-token', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        // Verify Refresh Token
        try {
            $token = $jwt->decode($request->request->get('token', ''));
        } catch (JWTException $exception) {
            if (JWT::ERROR_TOKEN_EXPIRED === $exception->getCode()) {
                throw new TokenExpiredException();
            }

            throw $exception;
        }

        // Generate New Token
        return ApiResponse::result()->setData(['token' => $jwt->encode(['id' => $token['id']])]);
    }

    #[Route(path: '/register', name: 'api_register', methods: ['POST'])]
    #[Thor(group: 'Security', desc: 'Register', requireAuth: false)]
    public function register(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/register-confirm/{token}', name: 'api_register_confirm', methods: ['GET'])]
    #[Thor(group: 'Security', desc: 'Account Confirmation', requireAuth: false)]
    public function confirmation(string $token): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/reset-request', name: 'api_reset_request', methods: ['POST'])]
    #[Thor(group: 'Security', desc: 'Reset Password Request', requireAuth: false)]
    public function resetRequest(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/reset-password/{token}', name: 'api_reset_password', methods: ['POST'])]
    #[Thor(group: 'Security', desc: 'Change Password', requireAuth: false)]
    public function resetPassword(string $token): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }
}
