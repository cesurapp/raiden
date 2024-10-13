<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use App\Tests\Setup\KernelTestCase;

class ProfileControllerTest extends KernelTestCase
{
    public function testShowProfile(): void
    {
        $user = $this->emSave($this->getUser());

        // Success Default User Type
        $this->login($user)
            ->jsonRequest('GET', '/v1/main/profile')
            ->isOk();

        // Success Admin Type
        $user->setType(UserType::ADMIN);
        $this->emPersist($user)
            ->emFlush()
            ->login($user)
            ->jsonRequest('GET', '/v1/main/profile')
            ->isOk()
            ->isJsonStructure(['data' => ['id', 'type' => 'ROLE_ADMIN']]);
    }

    public function testEditProfile(): void
    {
        /** @var User $user */
        $user = $this->emSave($this->getAdmin());

        // Fail Validation Exception
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/profile')
            ->isFail()
            ->isJsonStructure(['type', 'errors']);

        // Success
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/profile', [
                'email' => 'a'.$user->getEmail(),
                'phone' => $user->getPhone() + 1,
                'phone_country' => 'TR',
                'current_password' => '123123123',
                'password' => '123123123',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
            ->isOk()
            ->isEquals($user->getEmail(), 'data.email')
            ->isEquals($user->getPhone(), 'data.phone');
    }
}
