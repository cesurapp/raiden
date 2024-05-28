<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\AccountPermission;
use App\Admin\Core\Enum\UserType;
use App\Tests\Setup\KernelTestCase;

class AccountTest extends KernelTestCase
{
    public function testShowProfile(): void
    {
        $user = $this->emSave($this->getUser(true));

        // Fail Missing User Type
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/profile')
            ->isEquals('Access Denied.', 'message')
            ->isFail();

        // Success
        $user->setType(UserType::ADMIN);
        $this->emPersist($user)
            ->emFlush()
            ->login($user)
            ->jsonRequest('GET', '/v1/admin/account/profile')
            ->isOk()
            ->isJsonStructure(['data' => ['id', 'type' => 'ROLE_ADMIN']]);
    }

    public function testEditProfile(): void
    {
        /** @var User $user */
        $user = $this->emSave($this->getAdmin());

        // Fail Validation Exception
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/profile')
            ->isFail()
            ->isJsonStructure(['type', 'errors']);

        // Success
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/profile', [
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

    public function testAccountListing(): void
    {
        /** @var User $user */
        $user = $this->emSave($this->getAdmin());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager')
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_LIST));
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager')
            ->isOk()
            ->isJsonStructure(['data' => [['id']]]);

        // Export CSV
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager', [
                'export' => 'csv',
            ])
            ->isOk()
            ->isHeaderEquals('content-type.0', 'text/csv; charset=UTF-8')
            ->isContains('ID,"First Name"');

        // Export CSV Custom Field
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager', [
                'export' => 'csv',
                'export_field' => ['ID', 'Type'],
            ])
            ->isOk()
            ->isHeaderEquals('content-type.0', 'text/csv; charset=UTF-8')
            ->isContains('ID,Type'.PHP_EOL);

        // Create Empty User
        $this->emSave($this->getUser());

        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
                'filter' => ['type' => UserType::USER->value],
            ]))
            ->isOk()
            ->isJsonCount(1, 'data');

        // Sort ASC
        $this->emSave($this->getUser()->setFirstName('LastUser'));
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
                'sort' => 'ASC',
                'sort_by' => 'id',
            ]))
            ->isOk()
            ->isEquals('John', 'data.0.first_name');

        // Sort DESC
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager?'.http_build_query([
                'sort' => 'DESC',
                'sort_by' => 'id',
            ]))
            ->isOk()
            ->isEquals('LastUser', 'data.0.first_name');
    }

    public function testAccountCreate(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager')
            ->isFail();

        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_CREATE));

        // Fail Validation Exception
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager')
            ->isFail()
            ->isJsonStructure(['type', 'errors']);

        // Success
        $email = mt_rand().'@test.app';
        $phone = 541 .random_int(1000000, 4999999);
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', [
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
            ])
            ->isOk();

        // Duplicate Fail
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', [
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
            ])
            ->isFail()
            ->isEquals('This value is already used.', 'errors.email.0');

        // Fail Super Admin
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', [
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
            ])
            ->isFail();
    }

    public function testAccountEdit(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32())
            ->isFail();

        // Fail Validation Exception
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_EDIT));
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32())
            ->isFail()
            ->isJsonStructure(['type', 'errors']);

        // Success Same Email|Phone Update
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32(), [
                'email' => $user->getEmail(),
                'email_approved' => true,
                'phone' => $user->getPhone(),
                'phone_country' => 'TR',
                'phone_approved' => true,
                'type' => UserType::ADMIN->value,
                'password' => '123123123',
                'frozen' => false,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
            ->isOk();

        // Success
        $email = mt_rand().'@test.app';
        $phone = 541 .random_int(1000000, 4999999);
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32(), [
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
            ])
            ->isOk();

        // Check Email | Phone
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($phone, $user->getPhone());

        // Duplicate Email | Phone Fail
        $user2 = $this->emSave($this->emSave($this->getAdmin()));
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toBase32(), [
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
            ])
            ->isFail()
            ->isEquals('This value is already used.', 'errors.email.0');

        // Fail Edit Super Admin
        $superUser = $this->emSave($this->getUser()->setType(UserType::SUPERADMIN));
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$superUser->getId()->toBase32(), [
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
            ])
            ->isFail();
    }

    public function testAccountShow(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Fail Mıssing Permission
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toBase32())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_LIST));

        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toBase32())
            ->isOk()
            ->isJsonStructure(['data' => ['id']]);
    }

    public function testAccountDelete(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Create Removed User
        $userRemove = $this->emSave($this->getUser());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toBase32())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_DELETE));
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toBase32())
            ->isOk()
            ->isJsonStructure(['message' => ['success']]);

        // Fail Delete Super Admin
        $superUser = $this->emSave($this->getUser()->setType(UserType::SUPERADMIN));
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$superUser->getId()->toBase32())
            ->isFail();
    }

    public function testAccountEditPermission(): void
    {
        $user = $this->emSave(
            $this->getAdmin()
                ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST)
                ->addRoles(AccountPermission::ROLE_ACCOUNT_CREATE)
        );

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toBase32())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_PERMISSION));

        $permissions = [
            AccountPermission::ROLE_ACCOUNT_LIST->value,
            AccountPermission::ROLE_ACCOUNT_CREATE->value,
        ];
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toBase32(), [
                'permissions' => $permissions,
            ])
            ->isOk();
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertSame(array_intersect($user->getRoles(), $permissions), $permissions);
    }
}
