<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use App\Tests\Setup\AbstractKernelTestCase;

class CommandTest extends AbstractKernelTestCase
{
    public function testCreateUser(): void
    {
        static::bootKernel();

        $tester = $this->commandTester('user:create');
        $this->assertStringContainsString('User Created!', $tester->getDisplay());
    }

    public function testPasswordChangeUser(): void
    {
        $user = $this->createUser();

        // Change Password
        $tester = $this->commandTester('user:password', [$user->getEmail(), '123123123']);
        $this->assertStringContainsString('Password Changed!', $tester->getDisplay());

        // Check Password
        $hasher = static::getContainer()->get('security.user_password_hasher');
        $this->assertTrue($hasher->isPasswordValid($user, '123123123'));
    }

    public function testTypeChangeUser(): void
    {
        // Create User
        $user = $this->createUser();

        // Change Type
        $tester = $this->commandTester('user:type', [$user->getEmail(), 1]);
        $this->assertStringContainsString('Type Changed!', $tester->getDisplay());
        $this->assertEquals(UserType::ADMIN, $user->getType());
    }

    public function testRoleChangeUser(): void
    {
        // Create User
        $user = $this->createUser();
        $user->setType(UserType::ADMIN);
        $this->save($user);

        // Change Role
        $tester = $this->commandTester('user:role', [$user->getEmail(), 0]);
        $this->assertStringContainsString('Role Changed!', $tester->getDisplay());
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertTrue($user->hasRoles(UserType::ADMIN));
    }
}
