<?php

namespace App\Tests\Admin\Core;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Permission\CorePermission;
use App\Admin\Core\Permission\UserType;
use App\Tests\Setup\KernelTestCase;

class SecurityControllerTest extends KernelTestCase
{
    public function testLogin(): void
    {
        $user = $this->emSave($this->getUser());

        // Login Api
        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isOk()
            ->isJsonStructure(['data', 'token', 'refresh_token']);
    }

    public function testLoginPhone(): void
    {
        $user = $this->emSave($this->getUser());

        // Login Api
        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => (string) $user->getPhone(),
                'password' => '123123123',
            ])
            ->isOk()
            ->isJsonStructure(['data', 'token', 'refresh_token']);
    }

    public function testLoginToken(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Login Api
        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isOk()
            ->isJsonStructure(['token', 'refresh_token']);

        // Login with Token
        $this
            ->jsonRequest('GET', '/v1/main/profile', server: [
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getJson('token'),
            ])
            ->isOk();
    }

    public function testLogout(): void
    {
        $user = $this->emSave($this->getUser());

        // Logout Api
        $this->login($user)
            ->jsonRequest('POST', '/v1/auth/logout')
            ->isOk()
            ->isJsonStructure(['message']);

        $this->login($user)
            ->jsonRequest('POST', '/v1/auth/logout', [
                'refresh_token' => 'sdsadasdsasadasdsa',
            ], server: [
                'HTTP_ACCEPT_LANGUAGE' => 'tr-TR',
            ])
            ->isOk()
            ->isJsonStructure(['message'])
            ->isEquals('Oturumunuz kapatıldı', 'message.success.0');
    }

    public function testRefreshToken(): void
    {
        $user = $this->emSave($this->getAdmin());
        $jwt = self::getContainer()->get(JWT::class);

        // Login Api
        $this
            ->jsonRequest('POST', '/v1/auth/login', [
                'username' => $user->getEmail(),
                'password' => '123123123',
            ])
            ->isOk()
            ->isJsonStructure(['token', 'refresh_token']);

        $token = $this->getJson('token');
        $refreshToken = $this->getJson('refresh_token');

        // Validate Refresh Token Expiry
        $jwt->setTestTimestamp(time() + (86400 * 29));
        $jwt->decode($refreshToken);

        // Generate New Token
        usleep(100000);
        $newToken = $this
            ->jsonRequest('POST', '/v1/auth/refresh-token', ['refresh_token' => $refreshToken])
            ->isOk()
            ->getJson('data.token');
        $this->assertNotSame($token, $newToken);

        // Login with New Token
        $this
            ->jsonRequest('GET', '/v1/main/profile', server: [
                'HTTP_AUTHORIZATION' => 'Bearer '.$newToken,
            ])
            ->isOk();
    }

    public function testLoginOtp(): void
    {
        $user = $this->emSave($this->getUser());

        // Generate OTP Key Phone
        $this
            ->jsonRequest('PUT', '/v1/auth/login-otp', [
                'username' => $user->getPhone(),
            ])
            ->isOk();
        self::assertNotificationSubjectContains(self::getNotifierMessage(), 'Verification code');

        /** @var OtpKey $key */
        $key = $this->em()->getRepository(OtpKey::class)->findOneBy([
            'type' => OtpType::AUTH,
            'owner' => $user,
        ], ['id' => 'DESC']);

        // Login OTP Key Phone
        $this
            ->jsonRequest('POST', '/v1/auth/login-otp', [
                'username' => $user->getPhone(),
                'otp_key' => $key->getOtpKey(),
            ])
            ->isOk();

        // Retry Failed
        $this
            ->request('POST', '/v1/auth/login-otp', [
                'username' => $user->getPhone(),
                'otp_key' => $key->getOtpKey(),
            ])
            ->isFail();
    }

    public function testLoginOtpEmail(): void
    {
        $user = $this->emSave($this->getUser());

        // Generate OTP Key Phone
        $this
            ->request('PUT', '/v1/auth/login-otp', [
                'username' => $user->getEmail(),
            ])
            ->isOk();
        self::assertEmailSubjectContains(self::getMailerMessage(), 'Verification Code');

        /** @var OtpKey $key */
        $key = $this->em()->getRepository(OtpKey::class)->findOneBy([
            'type' => OtpType::AUTH,
            'owner' => $user,
        ], ['id' => 'DESC']);

        // Login OTP Key Phone
        $this
            ->jsonRequest('POST', '/v1/auth/login-otp', [
                'username' => $user->getEmail(),
                'otp_key' => $key->getOtpKey(),
            ])
            ->isOk();

        // Retry Failed
        $this
            ->jsonRequest('POST', '/v1/auth/login-otp', [
                'username' => $user->getEmail(),
                'otp_key' => $key->getOtpKey(),
            ])
            ->isFail();
    }

    public function testRegisterUser(): void
    {
        static::bootKernel();

        // Failed
        $this
            ->jsonRequest('POST', '/v1/auth/register')
            ->isValidationException()
            ->isJsonStructure([
                'errors' => [
                    'phone',
                    'password',
                    'first_name',
                    'last_name',
                ],
            ]);

        // Register Email
        $this
            ->jsonRequest('POST', '/v1/auth/register', [
                'email' => 'test@test.com',
                'password' => '123123123',
                'first_name' => 'Cesur',
                'last_name' => 'APAYDIN',
            ])
            ->isOk()
            ->isJsonStructure(['message' => ['success']]);

        // Check OTP & Welcome Email
        self::assertEmailCount(2);
        self::assertEmailSubjectContains(self::getMailerMessage(), 'Verification Code');
        self::assertEmailSubjectContains(self::getMailerMessage(1), 'Welcome to Raiden');

        // Register Email Duplicate
        $this->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test@test.com',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isFail();

        // Register Phone
        $this->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053420',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isOk()
            ->isJsonStructure(['message' => ['success']]);
        self::assertEmailCount(0);
        self::assertNotificationCount(1);
        self::assertNotificationSubjectContains(self::getNotifierMessage(), 'Verification code');

        // Register Phone Duplicate
        $this->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053420',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isFail();
    }

    public function testLoginRedirectOtp(): void
    {
        static::bootKernel();

        $this->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053421',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isOk();

        // OTP Key.
        $user = $this->em()->getRepository(User::class)->findOneBy(['phone' => '905414053421']);
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);

        // Password Login Failed
        $this->jsonRequest('POST', '/v1/auth/login', [
            'username' => (string) $user->getPhone(),
            'password' => '123123123',
        ])
            ->isEquals('AccountNotActivatedException', 'type');

        // Otp Login Success
        $this->jsonRequest('POST', '/v1/auth/login-otp', [
            'username' => $user->getPhone(),
            'otp_key' => $key->getOtpKey(),
        ])
            ->isOk();

        // OTP is USED
        $key = $this->em()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testConfirmPhone(): void
    {
        static::bootKernel();

        $this->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053491',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isOk();

        // OTP Key.
        $user = $this->em()->getRepository(User::class)->findOneBy(['phone' => '905414053491']);
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);

        // Failed
        $this->jsonRequest('POST', '/v1/auth/approve', [
            'username' => '905414053491',
            'otp_key' => 123123,
        ])
            ->isForbidden();

        // Success
        $this->jsonRequest('POST', '/v1/auth/approve', [
            'username' => '905414053491',
            'otp_key' => $key->getOtpKey(),
        ])
            ->isOk();

        // OTP is USED
        $key = $this->em()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testConfirmEmail(): void
    {
        static::bootKernel();

        $this->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test2@test3.com',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ])
            ->isOk();

        // OTP Key.
        $user = $this->em()->getRepository(User::class)->findOneBy(['email' => 'test2@test3.com']);
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);

        // Failed
        $this->jsonRequest('POST', '/v1/auth/approve', [
            'username' => 'test2@test3.com',
            'otp_key' => 123123,
        ])
            ->isForbidden();

        // Success
        $this->jsonRequest('POST', '/v1/auth/approve', [
            'username' => 'test2@test3.com',
            'otp_key' => $key->getOtpKey(),
        ])
            ->isOk();

        // OTP is USED
        $key = $this->em()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertTrue($user->isEmailApproved());
    }

    public function testResetRequest(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Failed Reset Request
        $this
            ->jsonRequest('POST', '/v1/auth/reset-request', [
                'username' => '90541'.random_int(1053420, 4053420),
            ])
            ->isNotFound();

        // Create Success Reset Request
        $this
            ->jsonRequest('POST', '/v1/auth/reset-request', [
                'username' => $user->getPhone(),
            ])
            ->isOk();
        self::assertNotificationSubjectContains(self::getNotifierMessage(), 'Verification code');

        // OTP Token
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);
        $this->assertNotNull($key);
    }

    public function testResetRequestEmail(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Failed Reset Request
        $this->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => random_int(100, 10000).'test@test.com',
        ])
            ->isNotFound();

        // Create Sucess Reset Request
        $this->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => $user->getEmail(),
        ])
            ->isOk();
        self::assertEmailSubjectContains(self::getMailerMessage(), 'Verification Code');

        // OTP Token
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);
        $this->assertNotNull($key);
    }

    public function testResetPassword(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Success Reset Request
        $this
            ->jsonRequest('POST', '/v1/auth/reset-request', ['username' => $user->getEmail()])
            ->isOk();
        self::assertEmailSubjectContains(self::getMailerMessage(), 'Verification Code');

        // User & Otp Key
        $user = $this->em()->getRepository(User::class)->findOneBy(['id' => $user->getId()]);
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::AUTH);

        // Reset Password
        $this->jsonRequest('POST', '/v1/auth/reset-password/', [
            'username' => $user->getEmail(),
            'otp_key' => $key->getOtpKey(),
            'password' => '123123123',
            'password_confirm' => '123123123',
        ])
            ->isOk();

        $key = $this->em()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());
    }

    public function testSwitchUser(): void
    {
        $user = $this->emSave($this->getUser()->setType(UserType::ADMIN)->addRoles(CorePermission::SWITCH_USER));
        $user2 = $this->emSave($this->getUser()->setType(UserType::ADMIN));

        // Access Denied without CorePermission::SWITCH_USER
        $this
            ->login($user2)
            ->jsonRequest('GET', '/v1/main/profile', server: [
                'HTTP_SWITCH_USER' => $user->getEmail(),
            ])
            ->isForbidden();

        // Access
        $this
            ->login($user)
            ->jsonRequest('GET', '/v1/main/profile', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ])
            ->isEquals($user2->getId()->toBase32(), 'data.id');

        // Disable SuperAdmin
        $userSuper = $this->emSave($this->getUser()->setType(UserType::SUPERADMIN));
        $this
            ->login($user)
            ->jsonRequest('GET', '/v1/main/profile', server: [
                'HTTP_SWITCH_USER' => $userSuper->getEmail(),
            ])
            ->isForbidden();

        // Disable Login Endpoint
        $this
            ->login($user)
            ->request('POST', '/v1/auth/login', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ])
            ->isForbidden();
    }
}
