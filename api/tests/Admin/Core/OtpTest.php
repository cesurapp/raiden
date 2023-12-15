<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Task\SendMailTask;
use App\Admin\Core\Task\SendSmsTask;
use App\Tests\Setup\KernelTestCase;
use Symfony\Component\Mime\Email;

class OtpTest extends KernelTestCase
{
    public function testOtpSingleType(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Email LOGIN OTP Key
        $key1 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::EMAIL)
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key1);

        // Create New LOGIN OTP Key
        $key2 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::EMAIL)
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key2);

        // Create Login Phone OTP Key
        $key3 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::PHONE)
            ->setOtpKey(143447)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key3);

        // Create Login Phone OTP Key
        $key4 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::PHONE)
            ->setOtpKey(143446)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->emSave($key4);

        // Check Refresh Token
        $this->em()->clear();
        $this->assertTrue($this->em()->find(OtpKey::class, $key1->getId())->isUsed());
        $this->assertFalse($this->em()->find(OtpKey::class, $key2->getId())->isUsed());

        $this->assertTrue($this->em()->find(OtpKey::class, $key3->getId())->isUsed());
        $this->assertFalse($this->em()->find(OtpKey::class, $key4->getId())->isUsed());
    }

    public function testOtpKeySendMail(): void
    {
        $user = $this->emSave($this->getUser());

        // Check Send Mail
        $this->mockTaskWorker(function ($object) {
            if (SendMailTask::class === $object['class']) {
                /** @var Email $mail */
                $mail = unserialize($object['payload']);
                $this->assertEquals('Verification Code', $mail->getSubject());
            }
        });

        // Create Email LOGIN OTP Key
        $this->emSave((new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::EMAIL)
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable()));
    }

    public function testOtpKeySendSms(): void
    {
        $user = $this->emSave($this->getUser());

        // Check Send Mail
        $this->mockTaskWorker(function ($object) use ($user) {
            if (SendSmsTask::class === $object['class']) {
                $payload = unserialize($object['payload']);

                $this->assertEquals($user->getPhone(), $payload['phone']);
                $this->assertEquals($user->getPhoneCountry(), $payload['countryCode']);
            }
        });

        // Create Email LOGIN OTP Key
        $this->emSave((new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::PHONE)
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable()));
    }
}
