<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Test\Setup\AbstractKernelTestCase;

class OtpTest extends AbstractKernelTestCase
{
    public function testOtpSingleType(): void
    {
        static::bootKernel();
        $user = $this->createUser();

        // Create LOGIN OTP Key
        $key1 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::LOGIN_EMAIL)
            ->setOtpKey(133449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->save($key1);

        // Create New LOGIN OTP Key
        $key2 = (new OtpKey())
            ->setOwner($user)
            ->setType(OtpType::LOGIN_EMAIL)
            ->setOtpKey(143449)
            ->setUsed(false)
            ->setExpiredAt(new \DateTimeImmutable());
        $this->save($key2);

        // Check Refresh Token
        $this->manager()->clear();
        $this->assertTrue($this->manager()->find(OtpKey::class, $key1->getId())->isUsed());
        $this->assertFalse($this->manager()->find(OtpKey::class, $key2->getId())->isUsed());
    }
}
