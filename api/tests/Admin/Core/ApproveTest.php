<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Tests\Setup\KernelTestCase;

class ApproveTest extends KernelTestCase
{
    public function testApproveEmail(): void
    {
        $user = $this->emSave($this->getUser());

        // Fail Account is Approved
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'email' => $user->getEmail(),
            ])
            ->isFail();

        // Approve Email Request
        $this->emSave($user->setEmailApproved(false));
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'email' => $user->getEmail(),
            ])
            ->isOk();

        // Find Code
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Approve Email Request
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/credentials', [
                'email' => $user->getEmail(),
                'otp_key' => $key->getOtpKey(),
            ])
            ->isOk();

        // Check
        $user = $this->em()->getRepository(User::class)->find($user->getId());
        $this->assertTrue($user->isEmailApproved());
    }

    public function testApprovePhone(): void
    {
        $user = $this->emSave($this->getUser()->setPhoneApproved(false));

        // Wrong Phone
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $user->getPhone().'22',
                'phone_country' => 'TR',
            ])
            ->isFail();

        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $user->getPhone(),
            ])
            ->isFail();

        // Approve Phone Request
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $user->getPhone(),
                'phone_country' => 'TR',
            ])
            ->isOk();

        // Find Code
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Approve Email Request
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/credentials', [
                'phone' => $user->getPhone(),
                'phone_country' => 'TR',
                'otp_key' => $key->getOtpKey(),
            ])
            ->isOk();

        // Check
        $user = $this->em()->getRepository(User::class)->find($user->getId());
        $this->assertTrue($user->isPhoneApproved());
    }

    public function testChangeEmail(): void
    {
        $user = $this->emSave($this->getUser());
        $user2 = $this->emSave($this->getUser());

        // Fail Already
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'email' => $user2->getEmail(),
            ])
            ->isFail();

        // Approve Email Request
        $email = 'asdsadas@asdsad.com';
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'email' => $email,
            ])
            ->isOk();

        // Find Code
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::EMAIL);

        // Approve Email Request
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/credentials', [
                'email' => $email,
                'otp_key' => $key->getOtpKey(),
            ])
            ->isOk();

        // Check
        $user = $this->em()->getRepository(User::class)->find($user->getId());
        $this->assertSame($user->getEmail(), $email);
        $this->assertTrue($user->isEmailApproved());
    }

    public function testChangePhone(): void
    {
        $user = $this->emSave($this->getUser()->setPhoneCountry('US'));
        $user2 = $this->emSave($this->getUser());

        // Fail Already
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $user2->getPhone(),
                'phone_country' => $user2->getPhoneCountry(),
            ])
            ->isFail();

        // Fail Required Country
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $user2->getPhone(),
            ])
            ->isFail();

        // Approve Email Request
        $phone = '905414053411';
        $phoneCountry = 'TR';
        $this->login($user)
            ->jsonRequest('PUT', '/v1/main/credentials', [
                'phone' => $phone,
                'phone_country' => $phoneCountry,
            ])
            ->isOk();

        // Find Code
        $key = $this->em()->getRepository(OtpKey::class)->getActiveKey($user, OtpType::PHONE);

        // Fail
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/credentials', [
                'phone' => $phone,
                'otp_key' => $key->getOtpKey(),
            ])
            ->isFail();

        // Approve Email Request
        $this->login($user)
            ->jsonRequest('POST', '/v1/main/credentials', [
                'phone' => $phone,
                'phone_country' => $phoneCountry,
                'otp_key' => $key->getOtpKey(),
            ])
        ->isOk();

        // Check
        $user = $this->em()->getRepository(User::class)->find($user->getId());
        $this->assertEquals($user->getPhone(), $phone);
        $this->assertEquals($user->getPhoneCountry(), $phoneCountry);
        $this->assertTrue($user->isPhoneApproved());
    }
}
