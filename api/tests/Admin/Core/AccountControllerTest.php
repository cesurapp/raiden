<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\AccountPermission;
use App\Admin\Core\Permission\UserType;
use App\Tests\Setup\KernelTestCase;

class AccountControllerTest extends KernelTestCase
{
    public function testListing(): void
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

    public function testCreate(): void
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
                'type' => UserType::ADMIN->value,
                'password' => '123123123',
                'frozen' => true,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
            ->isFail()
            ->isEquals('This value is already used.', 'errors.email.0');

        // Create Same Email SuperAdmin Fail
        // Create Same Email Merchant
        // Create Same Email Cargo

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

    public function testCreateSameEmailTypes(): void
    {
        $user = $this->emSave($this->getUser()->setType(UserType::SUPERADMIN));
        $data = [
            'email' => mt_rand().'@test.app',
            'email_approved' => true,
            'phone' => 541 .random_int(1000000, 4999999),
            'phone_country' => 'TR',
            'phone_approved' => true,
            'type' => UserType::SUPERADMIN->value,
            'password' => '123123123',
            'frozen' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        // Success SuperAdmin
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', $data)
            ->isOk();
        // Duplicate SuperAdmin Fail
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', $data)
            ->isEquals('This value is already used.', 'errors.email.0')
            ->isEquals('This value is already used.', 'errors.phone.0')
            ->isFail();
        // Duplicate Admin Fail
        $this->login($user)
            ->jsonRequest('POST', '/v1/admin/account/manager', array_merge($data, ['type' => UserType::ADMIN->value]))
            ->isEquals('This value is already used.', 'errors.email.0')
            ->isEquals('This value is already used.', 'errors.phone.0')
            ->isFail();
    }

    public function testEdit(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toString())
            ->isFail();

        // Fail Validation Exception
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_EDIT));
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toString())
            ->isFail()
            ->isJsonStructure(['type', 'errors']);

        // Success Same Email|Phone Update
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toString(), [
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
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toString(), [
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
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$user->getId()->toString(), [
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
            ->jsonRequest('PUT', '/v1/admin/account/manager/'.$superUser->getId()->toString(), [
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

    public function testShow(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Fail Mıssing Permission
        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toString())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_LIST));

        $this->login($user)
            ->jsonRequest('GET', '/v1/admin/account/manager/'.$user->getId()->toString())
            ->isOk()
            ->isJsonStructure(['data' => ['id']]);
    }

    public function testDelete(): void
    {
        $user = $this->emSave($this->getAdmin());

        // Create Removed User
        $userRemove = $this->emSave($this->getUser());

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toString())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_DELETE));
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$userRemove->getId()->toString())
            ->isOk()
            ->isJsonStructure(['message' => ['success']]);

        // Fail Delete Super Admin
        $superUser = $this->emSave($this->getUser()->setType(UserType::SUPERADMIN));
        $this->login($user)
            ->jsonRequest('DELETE', '/v1/admin/account/manager/'.$superUser->getId()->toString())
            ->isFail();
    }

    public function testEditPermission(): void
    {
        $user = $this->emSave(
            $this->getAdmin()
                ->addRoles(AccountPermission::ROLE_ACCOUNT_LIST)
                ->addRoles(AccountPermission::ROLE_ACCOUNT_CREATE)
        );

        // Fail Missing Permission
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toString())
            ->isFail();

        // Success
        $this->emSave($user->addRoles(AccountPermission::ROLE_ACCOUNT_PERMISSION));

        $permissions = [
            AccountPermission::ROLE_ACCOUNT_LIST->value,
            AccountPermission::ROLE_ACCOUNT_CREATE->value,
        ];
        $this->login($user)
            ->jsonRequest('PUT', '/v1/admin/account/permission/'.$user->getId()->toString(), [
                'permissions' => $permissions,
            ])
            ->isOk();
        $user = $this->em()->find(User::class, $user->getId());
        $this->assertSame(array_intersect($user->getRoles(), $permissions), $permissions);
    }
}
