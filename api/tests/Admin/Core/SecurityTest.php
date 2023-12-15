<?php

namespace App\Tests\Admin\Core;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\CorePermission;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Enum\UserType;
use App\Tests\Setup\AbstractWebTestCase;

class SecurityTest extends AbstractWebTestCase
{
    public function testLogin(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);

        // Assert
        $this->isOk();
        $this->assertJsonStructure(['data', 'token', 'refresh_token']);
    }

    public function testLoginPhone(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => (string) $user->getPhone(),
            'password' => '123123123',
        ]);

        // Assert
        $this->isOk();
        $this->assertJsonStructure(['data', 'token', 'refresh_token']);
    }

    public function testLoginToken(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['token', 'refresh_token']);

        // Login with Token
        $token = $this->json(key: 'token');
        $this->client()->jsonRequest('GET', '/v1/admin/account/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        $this->isOk();
    }

    public function testLogout(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Logout Api
        $this->client($user)->jsonRequest('POST', '/v1/auth/logout');
        $this->isOk();
        $this->assertJsonStructure(['message']);

        $this->client($user)->jsonRequest('POST', '/v1/auth/logout', [
            'refresh_token' => 'sdsadasdsasadasdsa',
        ], server: [
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR',
        ]);
        $this->isOk();
        $this->assertEquals('tr_TR', $this->client()->getRequest()->getLanguages()[0]);
        $this->assertEquals('Oturumunuz kapatıldı', $this->json(key: 'message')['success'][0]);
        $this->assertJsonStructure(['message']);
    }

    public function testRefreshToken(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);
        $jwt = self::getContainer()->get(JWT::class);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123123',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['token', 'refresh_token']);

        $token = $this->json(key: 'token');
        $refreshToken = $this->json(key: 'refresh_token');

        // Validate Refresh Token Expiry
        $jwt->setTestTimestamp(time() + (86400 * 29));
        $jwt->decode($refreshToken);

        // Generate New Token
        sleep(1);
        $this->client()->jsonRequest('POST', '/v1/auth/refresh-token', [
            'refresh_token' => $refreshToken,
        ]);
        $newToken = $this->json(key: 'data')['token'];
        $this->isOk();
        $this->assertNotSame($token, $newToken);

        // Login with New Token
        $this->client()->jsonRequest('GET', '/v1/admin/account/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$newToken,
        ]);
        $this->isOk();
    }

    public function testLoginOtp(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Generate OTP Key Phone
        $this->client()->request('PUT', '/v1/auth/login-otp', [
            'username' => $user->getPhone(),
        ]);
        $this->isOk();

        /** @var OtpKey $key */
        $key = $this->manager()->getRepository(OtpKey::class)->findOneBy([
            'type' => OtpType::PHONE,
            'owner' => $user,
        ], ['id' => 'DESC']);

        // Login OTP Key Phone
        $this->client()->request('POST', '/v1/auth/login-otp', [
            'username' => $user->getPhone(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Retry Failed
        $this->client()->request('POST', '/v1/auth/login-otp', [
            'username' => $user->getPhone(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isFail();
    }

    public function testLoginOtpEmail(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Generate OTP Key Phone
        $this->client()->request('PUT', '/v1/auth/login-otp', [
            'username' => $user->getEmail(),
        ]);
        $this->isOk();

        /** @var OtpKey $key */
        $key = $this->manager()->getRepository(OtpKey::class)->findOneBy([
            'type' => OtpType::EMAIL,
            'owner' => $user,
        ], ['id' => 'DESC']);

        // Login OTP Key Phone
        $this->client()->request('POST', '/v1/auth/login-otp', [
            'username' => $user->getEmail(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Retry Failed
        $this->client()->request('POST', '/v1/auth/login-otp', [
            'username' => $user->getEmail(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isFail();
    }

    public function testRegisterUser(): void
    {
        static::createClient();

        // Failed
        $this->client()->jsonRequest('POST', '/v1/auth/register');
        $this->isValidationException();
        $this->assertJsonStructure([
            'errors' => [
                'phone',
                'password',
                'first_name',
                'last_name',
            ],
        ]);

        // Register Email
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test@test.com',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['message' => ['success']]);

        // Register Email Duplicate
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test@test.com',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isFail();

        // Register Phone
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053420',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['message' => ['success']]);

        // Register Phone Duplicate
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053420',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isFail();
    }

    public function testLoginRedirectOtp(): void
    {
        static::createClient();
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053421',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isOk();

        // OTP Key.
        $user = $this->manager()->getRepository(User::class)->findOneBy(['phone' => '905414053421']);
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Password Login Failed
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => (string) $user->getPhone(),
            'password' => '123123123',
        ]);
        $this->assertSame('AccountNotActivatedException', $this->json(key: 'type'));

        // Otp Login Success
        $this->client()->request('POST', '/v1/auth/login-otp', [
            'username' => $user->getPhone(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // OTP is USED
        $key = $this->manager()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testConfirmPhone(): void
    {
        static::createClient();
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '905414053491',
            'phone_country' => 'TR',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isOk();

        // OTP Key.
        $user = $this->manager()->getRepository(User::class)->findOneBy(['phone' => '905414053491']);
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Failed
        $this->client()->jsonRequest('POST', '/v1/auth/approve', [
            'username' => '905414053491',
            'otp_key' => 123123,
        ]);
        $this->isForbidden();

        // Success
        $this->client()->jsonRequest('POST', '/v1/auth/approve', [
            'username' => '905414053491',
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // OTP is USED
        $key = $this->manager()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testConfirmEmail(): void
    {
        static::createClient();
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test2@test3.com',
            'password' => '123123123',
            'first_name' => 'Cesur',
            'last_name' => 'APAYDIN',
        ]);
        $this->isOk();

        // OTP Key.
        $user = $this->manager()->getRepository(User::class)->findOneBy(['email' => 'test2@test3.com']);
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Failed
        $this->client()->jsonRequest('POST', '/v1/auth/approve', [
            'username' => 'test2@test3.com',
            'otp_key' => 123123,
        ]);
        $this->isForbidden();

        // Success
        $this->client()->jsonRequest('POST', '/v1/auth/approve', [
            'username' => 'test2@test3.com',
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // OTP is USED
        $key = $this->manager()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());

        // Confirmed User
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertTrue($user->isEmailApproved());
    }

    public function testResetRequest(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Create Failed Reset Request
        self::getClient()->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => '90541'.random_int(1053420, 4053420),
        ]);
        $this->isNotFound();

        // Create Sucess Reset Request
        self::getClient()->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => $user->getPhone(),
        ]);
        $this->isOk();

        // OTP Token
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);
        $this->assertNotNull($key);
    }

    public function testResetRequestEmail(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Create Failed Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => random_int(100, 10000).'test@test.com',
        ]);
        $this->isNotFound();

        // Create Sucess Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', [
            'username' => $user->getEmail(),
        ]);
        $this->isOk();

        // OTP Token
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);
        $this->assertNotNull($key);
    }

    public function testResetPassword(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Create Sucess Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', ['username' => $user->getEmail()]);
        $this->isOk();

        // User & Otp Key
        $user = $this->manager()->getRepository(User::class)->findOneBy(['id' => $user->getId()]);
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Reset Password
        $this->client()->jsonRequest('POST', '/v1/auth/reset-password/', [
            'username' => $user->getEmail(),
            'otp_key' => $key->getOtpKey(),
            'password' => '123123123',
            'password_confirm' => '123123123',
        ]);
        $this->isOk();

        $key = $this->manager()->find(OtpKey::class, $key->getId());
        $this->assertTrue($key->isUsed());
    }

    public function testSwitchUser(): void
    {
        static::createClient();

        $user = $this->createUser()->setType(UserType::ADMIN)->addRoles(CorePermission::SWITCH_USER);
        $this->save($user);
        $user2 = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user2);

        // Access Denied without CorePermission::SWITCH_USER
        $this
            ->client($user2)
            ->request('GET', '/v1/admin/account/profile', server: [
                'HTTP_SWITCH_USER' => $user->getEmail(),
            ]);
        $this->isForbidden();

        // Acces
        $this
            ->client($user)
            ->request('GET', '/v1/admin/account/profile', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ]);
        $this->assertEquals($user2->getId()->toBase32(), $this->json(null, 'data')['id']);

        // Disable SuperAdmin
        $userSuper = $this->createUser()->setType(UserType::SUPERADMIN);
        $this->save($userSuper);
        $this
            ->client($user)
            ->request('GET', '/v1/admin/account/profile', server: [
                'HTTP_SWITCH_USER' => $userSuper->getEmail(),
            ]);
        $this->isForbidden();

        // Disable Login Endpoint
        $this
            ->client($user)
            ->request('POST', '/v1/auth/login', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ]);
        $this->isForbidden();
    }
}