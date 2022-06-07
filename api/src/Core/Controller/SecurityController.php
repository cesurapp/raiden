<?php

namespace App\Core\Controller;

use Ahc\Jwt\JWT;
use App\Core\Entity\User;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Response\ResponseTypeEnum;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'api_login')]
    #[Thor(group: 'Security', desc: 'Login')]
    public function login(#[CurrentUser] ?User $user): ApiResponse
    {
        if (!$user) {
            return ApiResponse::create(ResponseTypeEnum::ApiException, Response::HTTP_UNAUTHORIZED)
                ->setData('Invalid credentials.');
        }

        // Create Token
        $token = new JWT('secret', 'HS256', 1);

        return ApiResponse::create()->setData([
            'user' => $user,
            'token' => $token->encode(['id' => $user->getId()->toBase32()]),
        ]);
    }

    #[Route(path: '/logout', name: 'api_logout')]
    #[Thor(group: 'Security', desc: 'Logout')]
    public function logout(): void
    {
    }

    #[Route(path: '/refresh-token', name: 'api_refresh_token')]
    #[Thor(group: 'Security', desc: 'Login Refresh Token')]
    public function refreshToken(): ApiResponse
    {
        return ApiResponse::create();
    }

    #[Route(path: '/register', name: 'api_register')]
    #[Thor(group: 'Security', desc: 'Register')]
    public function register(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Route(path: '/confirm', name: 'api_confirm')]
    #[Thor(group: 'Security', desc: 'Account Confirmation')]
    public function confirmation(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Route(path: '/resetting', name: 'api_resetting')]
    #[Thor(group: 'Security', desc: 'Reset Password Request')]
    public function resetRequest(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }

    #[Route(path: '/reset-password', name: 'api_reset_password')]
    #[Thor(group: 'Security', desc: 'Change Password')]
    public function resetPassword(): ApiResponse
    {
        return ApiResponse::create()->setData(['']);
    }
}
