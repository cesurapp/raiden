<?php

namespace App\Core\Test;

class SecurityTest extends AbstractTestCase
{
    public function testLogin(): void
    {
        $user = $this->createUser(false);

        // Login Api
        $this->client()->jsonRequest('POST', '/api/v1/login', ['username' => $user->getEmail(), 'password' => '123123']);

        // Assert
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['user', 'token', 'refresh-token']);
    }

    public function testLoginToken(): void
    {
        $user = $this->createUser(false);

        // Login Api
        $this->client()->jsonRequest('POST', '/api/v1/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['token', 'refresh-token']);

        $token = $this->json(key: 'token');

        // Login with Token
        $this->client()->jsonRequest('GET', '/api/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
    }

    public function testRefreshToken(): void
    {
        $user = $this->createUser(false);

        // Login Api
        $this->client()->jsonRequest('POST', '/api/v1/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());
        $this->assertJsonStructure(['token', 'refresh-token']);

        $token = $this->json(key: 'token');

        // Login with Token
        $this->client()->jsonRequest('GET', '/api/v1/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
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

    public function testLogout(): void
    {
    }
}
