<?php

namespace App\Admin\Core\Controller;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Admin\Core\Dto\RegisterDto;
use App\Admin\Core\Dto\ResetPasswordDto;
use App\Admin\Core\Dto\ResetRequestDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Event\LoginEvent;
use App\Admin\Core\Event\RegisterEvent;
use App\Admin\Core\Event\ResetPasswordEvent;
use App\Admin\Core\Event\ResetRequestEvent;
use App\Admin\Core\Exception\RefreshTokenExpiredException;
use App\Admin\Core\Exception\TokenExpiredException;
use App\Admin\Core\Repository\RefreshTokenRepository;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Global Authorization Management.
 */
class SecurityController extends AbstractApiController
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

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
    public function login(#[CurrentUser] User $user, JWT $jwt, RefreshTokenRepository $repo, Request $request): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw new AccessDeniedException();
        }

        $this->dispatcher->dispatch(new LoginEvent($user), LoginEvent::NAME);

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

    #[Thor(
        group: 'Security',
        desc: 'Login with Refresh Token',
        request: [
            'refresh_token' => 'string',
        ],
        response: [
            200 => [
                'token' => 'string',
            ],
            RefreshTokenExpiredException::class,
        ],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/refresh-token', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw new AccessDeniedException();
        }

        // Verify Refresh Token
        try {
            $token = $jwt->decode($request->get('refresh_token'));
            if (!$repo->checkToken($request->get('refresh_token'), $token['exp'])) {
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

    #[Thor(group: 'Security', desc: 'Register', dto: RegisterDto::class, requireAuth: false)]
    #[Route(path: '/v1/auth/register', name: 'api_register', methods: ['POST'])]
    public function register(RegisterDto $register, UserRepository $userRepo, UserPasswordHasherInterface $hasher): ApiResponse
    {
        // Init & Save
        $user = $register->initObject(new User())->setPassword($register->validated('password'), $hasher);
        $userRepo->add($user);

        // Dispacth Event
        $this->dispatcher->dispatch(new RegisterEvent($user), RegisterEvent::NAME);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class)
            ->addMessage('Operation successful.');
    }

    #[Thor(group: 'Security', desc: 'Account Confirmation', requireAuth: false)]
    #[Route(path: '/v1/auth/confirm/{token}', name: 'api_register_confirm', methods: ['GET'])]
    public function confirmation(string $token, UserRepository $userRepo): ApiResponse
    {
        $user = $userRepo->findOneBy(['confirmationToken' => $token]);
        if (!$user) {
            throw new NotFoundHttpException('Token not found', code: 404);
        }

        // Confirm
        $userRepo->confirmUser($user);

        return ApiResponse::create()->addMessage('Your account has been confirmed.');
    }

    #[Thor(group: 'Security', desc: 'Reset Password Request', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-request', name: 'api_reset_request', methods: ['POST'])]
    public function resetRequest(ResetRequestDto $resetRequest, UserRepository $userRepo): ApiResponse
    {
        $user = $userRepo->loadUserByIdentifier($resetRequest->validated()['identity']);
        if (!$user) {
            throw new NotFoundHttpException('User not found.', code: 404);
        }

        // Disable 2 Hour
        if ($user->isPasswordRequestExpired()) {
            throw new AccessDeniedException('Wait two hours before a new request.');
        }

        // Create Token
        $userRepo->resetRequest($user);

        // Dispatch Event
        $this->dispatcher->dispatch(new ResetRequestEvent($user), ResetRequestEvent::NAME);

        return ApiResponse::create()->setData(['Operation successful.']);
    }

    #[Thor(group: 'Security', desc: 'Change Password', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-password/{resetToken}', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(User $resetToken, ResetPasswordDto $resetPassword, UserRepository $userRepo, UserPasswordHasherInterface $hasher): ApiResponse
    {
        // Update Password
        $userRepo->resetPassword($resetToken, $resetPassword->validated('password'), $hasher);

        // Dispatch Event
        $this->dispatcher->dispatch(new ResetPasswordEvent($resetToken), ResetPasswordEvent::NAME);

        return ApiResponse::create()->setData(['Operation successful.']);
    }
}
