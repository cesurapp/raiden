<?php

namespace App\Core\Controller;

use Ahc\Jwt\JWT;
use App\Core\Entity\User;
use App\Core\Repository\RefreshTokenRepository;
use App\Core\Resource\UserResource;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Global Authorization Management.
 */
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'api_login', methods: ['POST'])]
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

    #[Route(path: '/logout', name: 'api_logout', methods: ['GET'])]
    #[Thor(group: 'Security', desc: 'Logout', response: [
        200 => [
            'Operation successful.',
        ],
    ])]
    public function logout(): ApiResponse
    {
        return ApiResponse::msgSuccess()->setData('Operation successful.');
    }

    #[Route(path: '/refresh-token', name: 'api_refresh_token')]
    #[Thor(group: 'Security', desc: 'Login Refresh Token')]
    public function refreshToken(): ApiResponse
    {
        return ApiResponse::result();
    }

    #[Route(path: '/register', name: 'api_register')]
    #[Thor(group: 'Security', desc: 'Register')]
    public function register(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/confirm', name: 'api_confirm')]
    #[Thor(group: 'Security', desc: 'Account Confirmation')]
    public function confirmation(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/resetting', name: 'api_resetting')]
    #[Thor(group: 'Security', desc: 'Reset Password Request')]
    public function resetRequest(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }

    #[Route(path: '/reset-password', name: 'api_reset_password')]
    #[Thor(group: 'Security', desc: 'Change Password')]
    public function resetPassword(): ApiResponse
    {
        return ApiResponse::result()->setData(['']);
    }
}
