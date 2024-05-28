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
use App\Admin\Core\Exception\AccountNotActivatedException;
use App\Admin\Core\Exception\AccountSuspendedException;
use App\Admin\Core\Exception\OrganizationSuspendedException;
use App\Admin\Core\Exception\RefreshTokenExpiredException;
use App\Admin\Core\Exception\TokenExpiredException;
use App\Admin\Core\Repository\OtpKeyRepository;
use App\Admin\Core\Repository\RefreshTokenRepository;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Resource\UserResource;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Global Authorization Management.
 */
class SecurityController extends ApiController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository $userRepo,
        private readonly JWT $jwt,
        private readonly RefreshTokenRepository $refreshTokenRepo,
    ) {
    }

    #[Thor(
        stack: 'Security|1',
        title: 'Login - User & Password',
        request: [
            'username' => 'string',
            'password' => 'string',
        ],
        response: [
            200 => [
                'data' => UserResource::class,
                'token' => 'string',
                'refresh_token' => 'string',
            ],
            BadCredentialsException::class,
            TokenExpiredException::class,
            AccessDeniedException::class,
            AccountNotActivatedException::class,
            AccountSuspendedException::class,
            OrganizationSuspendedException::class,
        ],
        isAuth: false,
        order: 0
    )]
    #[Route(path: '/v1/auth/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user, Request $request): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw $this->createAccessDeniedException();
        }

        // Trigger Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::LOGIN);

        return ApiResponse::create()
            ->setData([
                'data' => $user,
                'token' => $this->jwt->encode(['id' => $user->getId()->toBase32()]),
                'refresh_token' => $this->refreshTokenRepo->createToken($user, $this->jwt)->getToken(),
            ])
            ->setResource(UserResource::class);
    }

    #[Thor(
        stack: 'Security',
        title: 'Login - Refresh Token',
        request: [
            'refresh_token' => 'string',
        ],
        response: [
            200 => ['data' => ['token' => 'string']],
            RefreshTokenExpiredException::class,
        ],
        isAuth: false,
        order: 1
    )]
    #[Route(path: '/v1/auth/refresh-token', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request, JWT $jwt, RefreshTokenRepository $repo): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw $this->createAccessDeniedException();
        }

        if (!$token = $request->get('refresh_token')) {
            throw new TokenNotFoundException('Refresh token not found!');
        }

        // Verify Refresh Token
        try {
            $decodedToken = $jwt->decode($token);
            if (!$repo->checkToken($token, $decodedToken['exp'])) {
                throw new RefreshTokenExpiredException();
            }
        } catch (JWTException $exception) {
            if (JWT::ERROR_TOKEN_EXPIRED === $exception->getCode()) {
                throw new RefreshTokenExpiredException();
            }

            throw $exception;
        }

        // Generate New Token
        return ApiResponse::create()->setData(['data' => ['token' => $jwt->encode(['id' => $decodedToken['id']])]]);
    }

    #[Thor(
        stack: 'Security',
        title: 'Login - Generate OTP key',
        dto: UsernameDto::class,
        isAuth: false,
        order: 2
    )]
    #[Route(path: '/v1/auth/login-otp', name: 'api_login_otp_request', methods: ['PUT'])]
    public function loginOtpRequest(UsernameDto $otpDto, OtpKeyRepository $otpKeyRepo, Request $request): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw $this->createAccessDeniedException();
        }

        if (!$user = $this->userRepo->loadUserByIdentifier($otpDto->validated('username'))) {
            throw $this->createNotFoundException('User not found!');
        }

        // Create OTP Key
        $otpKeyRepo->create($user, is_numeric($otpDto->validated('username')) ? OtpType::PHONE : OtpType::EMAIL);

        return ApiResponse::create()->addMessage('One-time login code has been sent');
    }

    #[Thor(
        stack: 'Security',
        title: 'Login - Using OTP Key',
        response: [
            200 => [
                'data' => UserResource::class,
                'token' => 'string',
                'refresh_token' => 'string',
            ],
        ],
        dto: UsernameOtpDto::class,
        isAuth: false,
        order: 3
    )]
    #[Route(path: '/v1/auth/login-otp', name: 'api_login_otp', methods: ['POST'])]
    public function loginOtp(UsernameOtpDto $otpDto, OtpKeyRepository $otpRepo, Request $request): ApiResponse
    {
        // Disable Switch User Generate Token
        if ($request->headers->has('switch-user')) {
            throw $this->createAccessDeniedException();
        }

        if (!$user = $this->userRepo->loadUserByIdentifier($otpDto->validated('username'))) {
            throw $this->createNotFoundException('User not found!');
        }

        $type = is_numeric($otpDto->validated('username')) ? OtpType::PHONE : OtpType::EMAIL;
        if (!$otpRepo->check($user, $type, $otpDto->validated('otp_key'))) {
            throw new BadCredentialsException('Wrong OTP key!', 403);
        }

        // Approve
        if (OtpType::PHONE === $type && !$user->isPhoneApproved()) {
            $user->setPhoneApproved(true);
            $this->userRepo->add($user);
        }
        if (OtpType::EMAIL === $type && !$user->isEmailApproved()) {
            $user->setEmailApproved(true);
            $this->userRepo->add($user);
        }

        return $this->login($user, $request);
    }

    #[Thor(
        stack: 'Security',
        title: 'Logout',
        request: [
            'refresh_token' => '?string',
        ],
        isAuth: false,
        order: 4
    )]
    #[Route(path: '/v1/auth/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request, RefreshTokenRepository $refreshTokenRepo): ApiResponse
    {
        if ($token = $request->get('refresh_token')) {
            $refreshTokenRepo->removeToken($token);
        }

        return ApiResponse::create()->addMessage('You are logged out');
    }

    #[Thor(
        stack: 'Security',
        title: 'Register - New Account',
        response: [
            200 => ['data' => UserResource::class],
        ],
        dto: RegisterDto::class,
        isAuth: false,
        order: 5
    )]
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
            ->addMessage('Your account is created');
    }

    #[Thor(
        stack: 'Security',
        title: 'Register - Phone & Email Approve',
        response: [
            NotFoundHttpException::class,
            BadCredentialsException::class,
        ],
        dto: UsernameOtpDto::class,
        isAuth: false,
        order: 6
    )]
    #[Route(path: '/v1/auth/approve', name: 'api_approve_account', methods: ['POST'])]
    public function approve(UsernameOtpDto $dto, OtpKeyRepository $otpRepo): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($dto->validated('username'))) {
            throw $this->createNotFoundException('User not found!');
        }

        // Check
        if (!$otp = $otpRepo->check($user, [OtpType::PHONE, OtpType::EMAIL], $dto->validated('otp_key'))) {
            throw new AccessDeniedHttpException('Wrong OTP key!');
        }

        // Approve
        $this->userRepo->approve($user, $otp);

        return ApiResponse::create()->addMessage('Your account has been approved');
    }

    #[Thor(
        stack: 'Security',
        title: 'Forgot Password - Reset Request',
        dto: UsernameDto::class,
        isAuth: false,
        order: 7
    )]
    #[Route(path: '/v1/auth/reset-request', name: 'api_reset_request', methods: ['POST'])]
    public function resetRequest(UsernameDto $usernameDto, OtpKeyRepository $otpRepo): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($usernameDto->validated('username'))) {
            throw $this->createNotFoundException('User not found!');
        }

        // Create OTP Key
        $type = is_numeric($usernameDto->validated('username')) ? OtpType::PHONE : OtpType::EMAIL;
        $otpRepo->create($user, $type, 60);

        // Dispatch Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::RESET_REQUEST);

        return ApiResponse::create()->addMessage('Your password reset code has been sent');
    }

    #[Thor(
        stack: 'Security',
        title: 'Forgot Password - Change Password',
        dto: ResetPasswordDto::class,
        isAuth: false,
        order: 8
    )]
    #[Route(path: '/v1/auth/reset-password/', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(ResetPasswordDto $dto, OtpKeyRepository $otpRepo, UserPasswordHasherInterface $hasher): ApiResponse
    {
        if (!$user = $this->userRepo->loadUserByIdentifier($dto->validated('username'))) {
            throw $this->createNotFoundException('User not found!');
        }

        // Check
        $type = is_numeric($dto->validated('username')) ? OtpType::PHONE : OtpType::EMAIL;
        if (!$otpRepo->check($user, $type, $dto->validated('otp_key'))) {
            throw $this->createAccessDeniedException('Wrong OTP key!');
        }

        // Update Password
        $user->setPassword($dto->validated('password'), $hasher);
        $this->userRepo->add($user);

        // Dispatch Event
        $this->dispatcher->dispatch(new SecurityEvent($user), SecurityEvent::RESET_PASSWORD);

        return ApiResponse::create()->addMessage('Your password was changed, you can login');
    }
}
