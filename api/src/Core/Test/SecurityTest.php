<?php

namespace App\Core\Test;

use Ahc\Jwt\JWT;

class SecurityTest extends AbstractTestCase
{
    public function testLogin(): void
    {
        $user = $this->createUser(false);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', ['username' => $user->getEmail(), 'password' => '123123']);

        // Assert
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['user', 'token', 'refresh_token']);
    }

    public function testLoginToken(): void
    {
        $user = $this->createUser(false);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['token', 'refresh_token']);

        $token = $this->json(key: 'token');

        // Login with Token
        $this->client()->jsonRequest('GET', '/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
    }

    public function testLogout(): void
    {
        $user = $this->createUser();

        // Logout Api
        $this->client()->jsonRequest('POST', '/v1/auth/logout', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['message']);

        // Language Test
        $this->client()->jsonRequest('POST', '/v1/auth/logout', ['username' => $user->getEmail(), 'password' => '123123'], server: [
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR',
        ]);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertEquals('tr_TR', $this->client()->getRequest()->getLanguages()[0]);
        $this->assertEquals('İşlem tamamlandı.', $this->json(key: 'message')['success'][0]);
        $this->assertJsonStructure(['message']);
    }

    public function testRefreshToken(): void
    {
        $user = $this->createUser(false);
        $jwt = self::getContainer()->get(JWT::class);

        // Login Api
        $this->client()->jsonRequest('POST', '/v1/auth/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['token', 'refresh_token']);

        $token = $this->json(key: 'token');
        $refreshToken = $this->json(key: 'refresh_token');

        // Validate Refresh Token Expiry
        $jwt->setTestTimestamp(time() + (86400 * 29));
        $jwt->decode($refreshToken);

        // Generate New Token
        sleep(1);
        $this->client()->jsonRequest('POST', '/v1/auth/refresh-token', [
            'token' => $refreshToken,
        ]);
        $newToken = $this->json(key: 'token');
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertNotSame($token, $newToken);

        // Login with New Token
        $this->client()->jsonRequest('GET', '/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$newToken,
        ]);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
    }

    public function testRegisterUser(): void
    {

    }

    public function testRegisterSeller(): void
    {
    }

    public function testConfirmation(): void
    {
    }

    public function testResetRequest(): void
    {
    }

    public function testResetPassword(): void
    {
    }
}
