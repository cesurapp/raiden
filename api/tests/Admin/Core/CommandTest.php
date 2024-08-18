<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use App\Tests\Setup\KernelTestCase;

class CommandTest extends KernelTestCase
{
    public function testCreateUser(): void
    {
        static::bootKernel();
        $tester = $this->commandTester('user:create');
        $this->assertStringContainsString('User Created!', $tester->getDisplay());
    }

    public function testPasswordChangeUser(): void
    {
        $user = $this->emSave($this->getUser());

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
        $user = $this->emSave($this->getUser());

        // Change Type
        $tester = $this->commandTester('user:type', [$user->getEmail(), 1]);
        $this->assertStringContainsString('Type Changed!', $tester->getDisplay());
        $this->assertEquals(UserType::ADMIN, $user->getType());
    }

    public function testRoleChangeUser(): void
    {
        // Create User
        $user = $this->emSave($this->getAdmin());

        // Change Role
        $tester = $this->commandTester('user:role', [$user->getEmail(), 0]);
        $this->assertStringContainsString('Role Changed!', $tester->getDisplay());
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertTrue($user->hasRoles(UserType::ADMIN));
    }
}
