<?php

namespace App\Core\Test;

class SecurityTest extends AbstractTestCase
{
    public function testLogin(): void
    {
        $user = $this->createUser(false);

        // Generate Token
        $this->getClient()->jsonRequest('GET', '/api/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode());

        $content = json_decode($this->getClient()->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue(isset($content['data']['token']));
    }

    public function testLoginToken(): void
    {
        $user = $this->createUser(false);

        // Generate Token
        $this->getClient()->jsonRequest('GET', '/api/login', ['username' => $user->getEmail(), 'password' => '123123']);
        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode());

        $content = json_decode($this->getClient()->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue(isset($content['data']['token']));

        // Login with Token
        $this->getClient()->jsonRequest('GET', '/api/profile', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$content['data']['token'],
        ]);
        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
    }

    public function testRefreshToken(): void
    {

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
