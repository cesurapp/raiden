<?php

namespace App\Admin\Core\Test;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\CorePermission;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Event\LoginEvent;
use App\Admin\Core\Event\RegisterEvent;
use App\Admin\Core\Event\ResetPasswordEvent;
use App\Admin\Core\Event\ResetRequestEvent;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class SecurityTest extends AbstractWebTestCase
{
    public function testLogin(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ]);

        // Assert
        $this->assertEventFired(LoginEvent::NAME);
        $this->isOk();
        $this->assertJsonStructure(['user', 'token', 'refresh_token']);
    }

    public function testLoginToken(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['token', 'refresh_token']);

        // Login with Token
        $token = $this->json(key: 'token');
        $this->client()->jsonRequest('GET', '/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        $this->isOk();
    }

    public function testLogout(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Logout Api
        $this->client()->jsonRequest('POST', '/v1/auth/logout', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ]);
        $this->isOk();
        $this->assertJsonStructure(['message']);

        // Language Test
        $this->client()->jsonRequest('POST', '/v1/auth/logout', [
            'username' => $user->getEmail(),
            'password' => '123123',
        ], server: [
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR',
        ]);
        $this->isOk();
        $this->assertEquals('tr_TR', $this->client()->getRequest()->getLanguages()[0]);
        $this->assertEquals('İşlem tamamlandı.', $this->json(key: 'message')['success'][0]);
        $this->assertJsonStructure(['message']);
    }

    public function testRefreshToken(): void
    {
        static::createClient();
        $user = $this->createUser(false);
        $jwt = self::getContainer()->get(JWT::class);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', [
            'username' => $user->getEmail(),
            'password' => '123123',
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
        $newToken = $this->json(key: 'token');
        $this->isOk();
        $this->assertNotSame($token, $newToken);

        // Login with New Token
        $this->client()->jsonRequest('GET', '/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$newToken,
        ]);
        $this->isOk();
    }

    public function testRegisterUser(): void
    {
        static::createClient();

        // Failed
        $this->client()->jsonRequest('POST', '/v1/auth/register');
        $this->isForbidden();
        $this->assertJsonStructure([
            'errors' => [
                'phone',
                'email',
                'password',
                'firstName',
                'lastName',
            ],
        ]);

        // Register Email
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'email' => 'test@test.com',
            'password' => '123123123',
            'firstName' => 'Ramazan',
            'lastName' => 'APAYDIN',
        ]);
        $this->isOk();
        $this->assertEventFired(RegisterEvent::NAME);
        $this->assertJsonStructure(['message' => ['success']]);

        // Register Phone
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '123123123',
            'password' => '123123123',
            'firstName' => 'Ramazan',
            'lastName' => 'APAYDIN',
        ]);
        $this->isOk();
        $this->assertEventFired(RegisterEvent::NAME);
        $this->assertJsonStructure(['message' => ['success']]);
    }

    public function testConfirmation(): void
    {
        static::createClient();
        $user = $this->createUser()->setApproved(false)->createConfirmationToken();
        $this->save($user);

        // Failed
        $this->client()->jsonRequest('GET', '/v1/auth/confirm/AA');
        $this->isNotFound();
        $this->assertExactJson([
            'type' => 'NotFoundHttpException',
            'code' => 404,
            'message' => 'Token not found',
        ]);

        // Success
        $this->client()->request('GET', '/v1/auth/confirm/'.$user->getConfirmationToken());
        $this->assertJsonStructure(['message' => ['success']]);

        // Failed
        $this->client()->request('GET', '/v1/auth/confirm/'.$user->getConfirmationToken());
        $this->isNotFound();
    }

    public function testRegisterAndConfirm(): void
    {
        static::createClient();

        // Register
        $this->client()->jsonRequest('POST', '/v1/auth/register', [
            'phone' => '5414053302',
            'password' => '123123123',
            'firstName' => 'Ramazan',
            'lastName' => 'APAYDIN',
        ]);
        $user = $this->manager()->getRepository(User::class)->findOneBy(['phone' => '5414053302']);
        $this->assertFalse($user->isApproved());

        // Confirm
        $this->client()->request('GET', '/v1/auth/confirm/'.$user->getConfirmationToken());
        $this->assertJsonStructure(['message' => ['success']]);
        $user = $this->manager()->getRepository(User::class)->findOneBy(['phone' => '5414053302']);
        $this->assertTrue($user->isApproved());
    }

    public function testResetRequest(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Create Failed Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', [
            'identity' => random_int(100, 10000).'test@test.com',
        ]);
        $this->isNotFound();

        // Create Sucess Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', [
            'identity' => $user->getEmail(),
        ]);
        $this->isOk();
        $this->assertEventFired(ResetRequestEvent::NAME);
    }

    public function testResetPassword(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Create Sucess Reset Request
        $this->client()->jsonRequest('POST', '/v1/auth/reset-request', ['identity' => $user->getEmail()]);
        $this->isOk();
        $user = $this->manager()->getRepository(User::class)->findOneBy(['id' => $user->getId()]);

        // Reset Password
        $this->client()->jsonRequest('POST', '/v1/auth/reset-password/'.$user->getResetToken(), [
            'password' => '123123123',
            'password_confirm' => '123123123',
        ]);
        $this->assertEventFired(ResetPasswordEvent::NAME);
        $this->isOk();

        $user = $this->manager()->getRepository(User::class)->findOneBy(['id' => $user->getId()]);
        $this->assertNull($user->getResetToken());
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
            ->client()
            ->loginUser($user2)
            ->request('GET', '/v1/admin/profile', server: [
                'HTTP_SWITCH_USER' => $user->getEmail(),
            ]);
        $this->isForbidden();

        static::ensureKernelShutdown();
        static::createClient();

        // Acces
        $this
            ->client()
            ->loginUser($user)
            ->request('GET', '/v1/admin/profile', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ]);
        $this->assertEquals($user2->getId()->toRfc4122(), $this->json()[0]['id']);

        static::ensureKernelShutdown();
        static::createClient();

        // Disable Login Endpoint
        $this
            ->client()
            ->loginUser($user)
            ->request('POST', '/v1/auth/login', server: [
                'HTTP_SWITCH_USER' => $user2->getEmail(),
            ]);
        $this->isForbidden();
    }
}
