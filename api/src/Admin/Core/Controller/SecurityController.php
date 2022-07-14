<?php

namespace App\Admin\Core\Controller;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Admin\Core\Dto\LoginOtpDto;
use App\Admin\Core\Dto\RegisterDto;
use App\Admin\Core\Dto\ResetPasswordDto;
use App\Admin\Core\Dto\UsernameDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Event\LoginEvent;
use App\Admin\Core\Event\LoginOtpRequestEvent;
use App\Admin\Core\Event\RegisterEvent;
use App\Admin\Core\Event\ResetPasswordEvent;
use App\Admin\Core\Event\ResetRequestEvent;
use App\Admin\Core\Exception\RefreshTokenExpiredException;
use App\Admin\Core\Exception\TokenExpiredException;
use App\Admin\Core\Repository\OtpKeyRepository;
use App\Admin\Core\Repository\RefreshTokenRepository;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository $userRepo
    ) {
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

    #[Thor(
        group: 'Security',
        desc: 'Login User with OTP Generate Key',
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/login-otp-request', name: 'api_login_otp_request', methods: ['POST'])]
    public function loginOtpRequest(UsernameDto $usernameDto, OtpKeyRepository $otpKeyRepo): ApiResponse
    {
        $user = $this->userRepo->loadUserByIdentifier($usernameDto->validated('username'));
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        $this->dispatcher->dispatch(
            new LoginOtpRequestEvent($user, $otpKeyRepo->create($user, OtpType::LOGIN)),
            LoginOtpRequestEvent::NAME
        );

        return ApiResponse::create()->addMessage('Operation successful.');
    }

    #[Thor(
        group: 'Security',
        desc: 'Login User with OTP',
        response: [
            200 => [
                'user' => UserResource::class,
                'token' => 'string',
                'refresh_token' => 'string',
            ],
        ],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/login-otp', name: 'api_login_otp', methods: ['POST'])]
    public function loginOtp(LoginOtpDto $otpDto, OtpKeyRepository $otpKeyRepo): ApiResponse|Response
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($otpDto->validated('username'))) {
            throw new NotFoundHttpException('User not found.');
        }

        if (!$otpKeyRepo->check($user, OtpType::LOGIN, $otpDto->validated('otp_key'))) {
            throw new BadCredentialsException('Wrong otp key.', 403);
        }

        return $this->forward('App\Admin\Core\Controller\SecurityController::login', [
            'user' => $user,
        ]);
    }

    #[Thor(
        group: 'Security',
        desc: 'Logout',
        request: [
            'refresh_token' => '?string',
        ],
        requireAuth: true
    )]
    #[Route(path: '/v1/auth/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request, RefreshTokenRepository $refreshTokenRepo): ApiResponse
    {
        if (!$this->getUser()) {
            throw new AccessDeniedException();
        }

        if ($token = $request->get('refresh_token')) {
            $refreshTokenRepo->removeToken($token);
        }

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
    public function register(
        RegisterDto $register,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher
    ): ApiResponse {
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

    #[Thor(
        group: 'Security',
        desc: 'Account Phone|Email Approve',
        request: ['approve_key' => 'string'],
        response: [
            AccessDeniedException::class,
            BadCredentialsException::class,
        ],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/approve/{id}', name: 'api_approve_account', methods: ['POST'])]
    public function approve(Request $request, User $user, UserRepository $userRepo): ApiResponse
    {
        if (!$approve_key = $request->get('approve_key')) {
            throw new BadRequestException('approve_key not found!');
        }

        // Approve
        if (!$userRepo->approve($user, $approve_key)) {
            throw new AccessDeniedException();
        }

        return ApiResponse::create()->addMessage('Your account has been approved.');
    }

    #[Thor(
        group: 'Security',
        desc: 'Reset Password Request',
        request: ['username' => 'string'],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/reset-request', name: 'api_reset_request', methods: ['POST'])]
    public function resetRequest(UsernameDto $resetRequest, UserRepository $userRepo): ApiResponse
    {
        $user = $userRepo->loadUserByIdentifier($resetRequest->validated()['username']);
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Create Reset Key
        $userRepo->resetRequest($user);

        // Dispatch Event
        $this->dispatcher->dispatch(new ResetRequestEvent($user), ResetRequestEvent::NAME);

        return ApiResponse::create()->setData(['Operation successful.']);
    }

    #[Thor(group: 'Security', desc: 'Change Password', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-password/', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        User $resetToken,
        ResetPasswordDto $resetPassword,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher
    ): ApiResponse {
        // Update Password
        $userRepo->resetPassword($resetToken, $resetPassword->validated('password'), $hasher);

        // Dispatch Event
        $this->dispatcher->dispatch(new ResetPasswordEvent($resetToken), ResetPasswordEvent::NAME);

        return ApiResponse::create()->setData(['Operation successful.']);
    }
}
