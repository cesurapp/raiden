<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Test\Setup\AbstractWebTestCase;

class ApproveTest extends AbstractWebTestCase
{
    public function testApproveEmail(): void
    {
        static::createClient();
        $user = $this->createUser();

        // Fail Account is Approved
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'email' => $user->getEmail(),
        ]);
        $this->isFail();

        $user->setEmailApproved(false);
        $this->save($user);

        // Approve Email Request
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'email' => $user->getEmail(),
        ]);
        $this->isOk();

        // Find Code
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Approve Email Request
        $this->client($user)->jsonRequest('POST', '/v1/main/credentials', [
            'email' => $user->getEmail(),
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Check
        $user = $this->manager()->getRepository(User::class)->find($user->getId());
        $this->assertTrue($user->isEmailApproved());
    }

    public function testApprovePhone(): void
    {
        static::createClient();
        $user = $this->createUser();
        $user->setPhoneApproved(false);
        $this->save($user);

        // Wrong Phone
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $user->getPhone().'22',
            'phone_country' => 'TR',
        ]);
        $this->isFail();
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $user->getPhone(),
        ]);
        $this->isFail();

        // Approve Phone Request
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $user->getPhone(),
            'phone_country' => 'TR',
        ]);
        $this->isOk();

        // Find Code
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Approve Email Request
        $this->client($user)->jsonRequest('POST', '/v1/main/credentials', [
            'phone' => $user->getPhone(),
            'phone_country' => 'TR',
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Check
        $user = $this->manager()->getRepository(User::class)->find($user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testChangeEmail(): void
    {
        static::createClient();
        $user = $this->createUser();
        $user2 = $this->createUser();

        // Fail Already
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'email' => $user2->getEmail(),
        ]);
        $this->isFail();

        // Approve Email Request
        $email = 'asdsadas@asdsad.com';
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'email' => $email,
        ]);
        $this->isOk();

        // Find Code
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Approve Email Request
        $this->client($user)->jsonRequest('POST', '/v1/main/credentials', [
            'email' => $email,
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Check
        $user = $this->manager()->getRepository(User::class)->find($user->getId());
        $this->assertSame($user->getEmail(), $email);
        $this->assertTrue($user->isEmailApproved());
    }

    public function testChangePhone(): void
    {
        static::createClient();
        $user = $this->createUser();
        $user2 = $this->createUser();

        $user->setPhoneCountry('US');
        $this->save($user);

        // Fail Already
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $user2->getPhone(),
            'phone_country' => $user2->getPhoneCountry(),
        ]);
        $this->isFail();

        // Fail Required Country
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $user2->getPhone(),
        ]);
        $this->isFail();

        // Approve Email Request
        $phone = '905414053411';
        $phoneCountry = 'TR';
        $this->client($user)->jsonRequest('PUT', '/v1/main/credentials', [
            'phone' => $phone,
            'phone_country' => $phoneCountry,
        ]);
        $this->isOk();

        // Find Code
        $key = $this->manager()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Fail
        $this->client($user)->jsonRequest('POST', '/v1/main/credentials', [
            'phone' => $phone,
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isFail();

        // Approve Email Request
        $this->client($user)->jsonRequest('POST', '/v1/main/credentials', [
            'phone' => $phone,
            'phone_country' => $phoneCountry,
            'otp_key' => $key->getOtpKey(),
        ]);
        $this->isOk();

        // Check
        $user = $this->manager()->getRepository(User::class)->find($user->getId());
        $this->assertEquals($user->getPhone(), $phone);
        $this->assertEquals($user->getPhoneCountry(), $phoneCountry);
        $this->assertTrue($user->isPhoneApproved());
    }
}
