<?php

namespace App\Admin\Core\Controller;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Admin\Core\Dto\UsernameOtpDto;
use App\Admin\Core\Dto\RegisterDto;
use App\Admin\Core\Dto\ResetPasswordDto;
use App\Admin\Core\Dto\UsernameDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Event\SecurityEvent;
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
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository $userRepo,
        private readonly JWT $jwt,
        private readonly RefreshTokenRepository $refreshTokenRepo,
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
    public function login(#[CurrentUser] User $user, Request $request): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw new AccessDeniedException();
        }

        // Trigger Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::LOGIN);

        return ApiResponse::create()
            ->setData([
                'user' => $user,
                'token' => $this->jwt->encode(['id' => $user->getId()->toBase32()]),
                'refresh_token' => $this->refreshTokenRepo->createToken($user, $this->jwt)->getToken(),
            ])
            ->setResource(UserResource::class);
    }

    #[Thor(
        group: 'Security',
        desc: 'Generate OTP key for User Login.',
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/login-otp', name: 'api_login_otp_request', methods: ['PUT'])]
    public function loginOtpRequest(UsernameDto $otpDto, OtpKeyRepository $otpKeyRepo): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($otpDto->validated('username'))) {
            throw new NotFoundHttpException('User not found!');
        }

        // Create OTP Key
        $otpKeyRepo->create($user, OtpType::LOGIN);

        return ApiResponse::create()->addMessage('Operation successful.');
    }

    #[Thor(
        group: 'Security',
        desc: 'Login User with OTP Key',
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
    public function loginOtp(UsernameOtpDto $otpDto, OtpKeyRepository $otpRepo, Request $request): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($otpDto->validated('username'))) {
            throw new NotFoundHttpException('User not found!');
        }

        if (!$otpRepo->check($user, OtpType::LOGIN, $otpDto->validated('otp_key'))) {
            throw new BadCredentialsException('Wrong OTP key!', 403);
        }

        return $this->login($user, $request);
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
    public function register(RegisterDto $register, UserPasswordHasherInterface $hasher): ApiResponse
    {
        // Init & Save
        $user = $register->initObject(new User())->setPassword($register->validated('password'), $hasher);
        $this->userRepo->add($user);

        // Dispacth Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::REGISTER);

        return ApiResponse::create()
            ->setData($user)
            ->setResource(UserResource::class)
            ->addMessage('Operation successful.');
    }

    #[Thor(
        group: 'Security',
        desc: 'Account Phone|Email Approve',
        response: [
            NotFoundHttpException::class,
            BadCredentialsException::class,
        ],
        requireAuth: false
    )]
    #[Route(path: '/v1/auth/approve', name: 'api_approve_account', methods: ['POST'])]
    public function approve(UsernameOtpDto $dto, OtpKeyRepository $otpRepo): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($dto->validated('username'))) {
            throw new NotFoundHttpException('User not found!');
        }

        // Check
        if (!$otp = $otpRepo->check($user, [OtpType::REGISTER_PHONE, OtpType::REGISTER_EMAIL], $dto->validated('otp_key'))) {
            throw new AccessDeniedException('Wrong OTP key!');
        }

        // Approve
        $this->userRepo->approve($user, $otp);

        return ApiResponse::create()->addMessage('Your account has been approved.');
    }

    #[Thor(group: 'Security', desc: 'Forgot Password - Reset Request', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-request', name: 'api_reset_request', methods: ['POST'])]
    public function resetRequest(UsernameDto $usernameDto, OtpKeyRepository $otpRepo): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($usernameDto->validated('username'))) {
            throw new NotFoundHttpException('User not found!');
        }

        // Create OTP Key
        $otpRepo->create($user, OtpType::RESETTING, 60);

        // Dispatch Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::RESET_REQUEST);

        return ApiResponse::create()->addMessage('Operation successful.');
    }

    #[Thor(group: 'Security', desc: 'Forgot Password - Change Password', requireAuth: false)]
    #[Route(path: '/v1/auth/reset-password/', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(ResetPasswordDto $dto, OtpKeyRepository $otpRepo, UserPasswordHasherInterface $hasher): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($dto->validated('username'))) {
            throw new NotFoundHttpException('User not found!');
        }

        // Check
        if (!$otpRepo->check($user, OtpType::RESETTING, $dto->validated('otp_key'))) {
            throw new AccessDeniedException('Wrong OTP key!');
        }

        // Update Password
        $user->setPassword($dto->validated('password'), $hasher);
        $this->userRepo->add($user);

        // Dispatch Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::RESET_PASSWORD);

        return ApiResponse::create()->addMessage('Operation successful.');
    }
}
