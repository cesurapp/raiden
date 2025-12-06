<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Task\SendMailTask;
use App\Admin\Core\Task\SendSmsTask;
use App\Tests\Setup\KernelTestCase;
use Symfony\Component\Mime\Email;

class OtpKeyTest extends KernelTestCase
{
    public function testOtpSingleType(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Email LOGIN OTP Key
        $key1 = new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress($user->getEmail())
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key1);

        // Create New LOGIN OTP Key
        $key2 = new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress($user->getEmail())
            ->setOtpKey(131449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key2);

        $this->assertTrue($this->refresh($key1)->isUsed());
        $this->assertFalse($this->refresh($key2)->isUsed());

        // Create Login Phone OTP Key
        $key3 = new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress((string) $user->getPhone())
            ->setPhoneCountry($user->getPhoneCountry())
            ->setOtpKey(143447)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key3);

        // Create Login Phone OTP Key
        $key4 = new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress($user->getPhone())
            ->setPhoneCountry($user->getPhoneCountry())
            ->setOtpKey(144446)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key4);

        // Check Refresh Token
        $this->assertTrue($this->refresh($key3)->isUsed());
        $this->assertFalse($this->refresh($key4)->isUsed());
    }

    public function testOtpKeySendMail(): void
    {
        $user = $this->emSave($this->getUser());

        // Check Send Mail
        $mock = $this->mockTaskWorker(function ($object) {
            if (SendMailTask::class === $object['class']) {
                /** @var Email $mail */
                $mail = unserialize($object['payload']);
                $this->assertEquals('Verification Code', $mail->getSubject());
            }
        });
        $mock->expects($this->once())->method('handle');

        // Create Email LOGIN OTP Key
        $this->emSave(new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress($user->getEmail())
            ->setOtpKey(133422)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable()));
    }

    public function testOtpKeySendSms(): void
    {
        $user = $this->emSave($this->getUser());

        // Check Send Mail
        $mock = $this->mockTaskWorker(function ($object) use ($user) {
            if (SendSmsTask::class === $object['class']) {
                $payload = unserialize($object['payload']);

                $this->assertEquals($user->getPhone(), $payload['phone']);
                $this->assertEquals($user->getPhoneCountry(), $payload['countryCode']);
            }
        });
        $mock->expects($this->once())->method('handle');

        // Create Email LOGIN OTP Key
        $this->emSave(new OtpKey()
            ->setOwner($user)
            ->setType(OtpType::AUTH)
            ->setAddress((string) $user->getPhone())
            ->setPhoneCountry($user->getPhoneCountry())
            ->setOtpKey(233449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable()));
    }
}
