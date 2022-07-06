<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Test\Setup\AbstractKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class CommandTest extends AbstractKernelTestCase
{
    public function testCreateUser(): void
    {
        $tester = $this->cmdTester('user:create');
        $this->assertStringContainsString('User Created!', $tester->getDisplay());
    }

    public function testPasswordChangeUser(): void
    {
        $user = $this->createUser();

        // Change Password
        $tester = $this->cmdTester('user:password', [$user->getEmail(), '123123123']);
        $this->assertStringContainsString('Password Changed!', $tester->getDisplay());

        // Check Password
        $hasher = new PasswordHasherFactory([PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto']]);
        $this->assertTrue((new UserPasswordHasher($hasher))->isPasswordValid($user, '123123123'));
    }

    public function testTypeChangeUser(): void
    {
        // Create User
        $user = $this->createUser();

        // Change Type
        $tester = $this->cmdTester('user:type', [$user->getEmail(), 1]);
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
        $tester = $this->cmdTester('user:role', [$user->getEmail(), 0]);
        $this->assertStringContainsString('Role Changed!', $tester->getDisplay());
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertTrue($user->hasRoles(UserType::ADMIN));
    }
}
