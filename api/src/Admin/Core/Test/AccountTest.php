<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\AccountPermission;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class AccountTest extends AbstractWebTestCase
{
    public function testShowProfile(): void
    {
        static::createClient();
        $user = $this->createUser(true);
        $this->save($user);

        // Fail Missing User Type
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/profile');
        $this->isFail();

        // Success
        $user->setType(UserType::ADMIN);
        $this->save($user);
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/profile');
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testEditProfile(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Validation Exception
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/profile');
        $this->isFail();
        $this->assertJsonStructure(['type', 'errors']);

        // Success
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/profile', [
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'phone_country' => 'TR',
            'current_password' => '123123123',
            'password' => '123123123',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isOk();
    }

    public function testAccountListing(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Missing Permission
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager');
        $this->isFail();

        // Success
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager');
        $this->isOk();
        $this->assertJsonStructure(['data' => [['id']]]);

        // Export CSV
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager', [
            'export' => 'csv',
        ]);
        $this->isOk();
        $this->assertEquals('text/csv; charset=UTF-8', $this->client()->getInternalResponse()->getHeader('content-type'));
        $this->assertStringContainsString('ID,"First Name"', $this->client()->getInternalResponse()->getContent());

        // Export CSV Custom Field
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager', [
            'export' => 'csv',
            'export_field' => ['ID', 'Type'],
        ]);
        $this->isOk();
        $this->assertEquals('text/csv; charset=UTF-8', $this->client()->getInternalResponse()->getHeader('content-type'));
        $this->assertStringContainsString('ID,Type'.PHP_EOL, $this->client()->getInternalResponse()->getContent());

        // Filter
        $this->createUser();
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
            'filter' => [
                'type' => UserType::USER->value,
            ],
        ]));
        $this->isOk();
        $this->assertJsonCount(1, 'data');

        // Sort ASC
        $this->save($this->createUser()->setFirstName('LastUser'));
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
                'sort' => 'ASC',
                'sort_by' => 'id',
            ]));
        $this->isOk();
        $this->assertEquals('John', $this->json(null, 'data')[0]['first_name']);

        // Sort DESC
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
                'sort' => 'DESC',
                'sort_by' => 'id',
            ]));
        $this->isOk();
        $this->assertEquals('LastUser', $this->json(null, 'data')[0]['first_name']);
    }

    public function testAccountCreate(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Missing Permission
        $this->client($user)->jsonRequest('POST', '/v1/admin/account/manager');
        $this->isFail();

        $user->addRoles(AccountPermission::ROLE_ACCOUNT_CREATE);
        $this->save($user);

        // Fail Validation Exception
        $this->client($user)->jsonRequest('POST', '/v1/admin/account/manager');
        $this->isFail();
        $this->assertJsonStructure(['type', 'errors']);

        // Success
        $email = mt_rand().'@test.app';
        $phone = 541 .random_int(1000000, 4999999);
        $this->client($user)->jsonRequest('POST', '/v1/admin/account/manager', [
            'email' => $email,
            'email_approved' => true,
            'phone' => $phone,
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => UserType::ADMIN->value,
            'password' => '123123123',
            'frozen' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isOk();

        // Duplicate Fail
        $this->client($user)->jsonRequest('POST', '/v1/admin/account/manager', [
            'email' => $email,
            'email_approved' => true,
            'phone' => $phone,
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => 'admin',
            'password' => '123123123',
            'frozen' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isFail();
        $this->assertEquals('This value is already used.', $this->json(null, 'errors')['email'][0]);

        // Fail Super Admin
        $this->client($user)->jsonRequest('POST', '/v1/admin/account/manager', [
            'email' => mt_rand().'@test.app',
            'email_approved' => true,
            'phone' => 541 .random_int(1000000, 4999999),
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => 'super_admin',
            'password' => '123123123',
            'frozen' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isFail();
    }

    public function testAccountEdit(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Missing Permission
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32());
        $this->isFail();

        // Fail Validation Exception
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_EDIT);
        $this->save($user);
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32());
        $this->isFail();
        $this->assertJsonStructure(['type', 'errors']);

        // Success
        $email = mt_rand().'@test.app';
        $phone = 541 .random_int(1000000, 4999999);
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32(), [
            'email' => $email,
            'email_approved' => true,
            'phone' => $phone,
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => UserType::ADMIN->value,
            'password' => '123123123',
            'frozen' => false,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isOk();

        // Check Email | Phone
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($phone, $user->getPhone());

        // Duplicate Email | Phone Fail
        $user2 = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user2);
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32(), [
            'email' => $user2->getEmail(),
            'email_approved' => true,
            'phone' => $user2->getPhone(),
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => UserType::ADMIN->value,
            'password' => '123123123',
            'frozen' => false,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isFail();
        $this->assertEquals('This value is already used.', $this->json(null, 'errors')['email'][0]);

        // Fail Edit Super Admin
        $superUser = $this->createUser()->setType(UserType::SUPERADMIN);
        $this->save($superUser);
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/manager/'.$superUser->getId()->toBase32(), [
            'email' => $user2->getEmail(),
            'email_approved' => true,
            'phone' => $user2->getPhone(),
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => UserType::ADMIN->value,
            'password' => '123123123',
            'frozen' => false,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->isFail();
    }

    public function testAccountShow(): void
    {
        static::createClient();
        $user = $this->createUser(true)->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Mıssing Permission
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toBase32());
        $this->isFail();

        // Success
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_LIST);
        $this->save($user);

        $this->client($user)->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['data' => ['id']]);
    }

    public function testAccountDelete(): void
    {
        static::createClient();
        $user = $this->createUser(true)->setType(UserType::ADMIN);
        $this->save($user);

        // Create Removed User
        $userRemove = $this->createUser();

        // Fail Missing Permission
        $this->client($user)->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toBase32());
        $this->isFail();

        // Success
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_DELETE);
        $this->save($user);
        $this->client($user)->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toBase32());
        $this->isOk();
        $this->assertJsonStructure(['message' => ['success']]);

        // Fail Delete Super Admin
        $superUser = $this->createUser()->setType(UserType::SUPERADMIN);
        $this->save($superUser);
        $this->client($user)->jsonRequest('DELETE', '/v1/admin/account/manager/'.$superUser->getId()->toBase32());
        $this->isFail();
    }

    public function testAccountShowPermission(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Missing Permission
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/permission/'.$user->getId()->toBase32());
        $this->isFail();

        // Success
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_PERMISSION);
        $this->save($user);
        $this->client($user)->jsonRequest('GET', '/v1/admin/account/permission/'.$user->getId()->toBase32());
        $this->isOk();
    }

    public function testAccountEditPermission(): void
    {
        static::createClient();
        $user = $this->createUser()->setType(UserType::ADMIN);
        $this->save($user);

        // Fail Missing Permission
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toBase32());
        $this->isFail();

        // Success
        $user->addRoles(AccountPermission::ROLE_ACCOUNT_PERMISSION);
        $this->save($user);
        $permissions = [
            AccountPermission::ROLE_ACCOUNT_LIST->value,
            AccountPermission::ROLE_ACCOUNT_CREATE->value,
        ];
        $this->client($user)->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toBase32(), [
            'permissions' => $permissions,
        ]);
        $this->isOk();
        $user = $this->manager()->find(User::class, $user->getId());
        $this->assertSame(array_intersect($user->getRoles(), $permissions), $permissions);
    }
}
