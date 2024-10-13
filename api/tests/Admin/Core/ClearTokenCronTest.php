<?php

namespace App\Tests\Admin\Core;

use App\Admin\Core\Cron\ClearExpiredOtpKeyCron;
use App\Admin\Core\Cron\ClearExpiredTokenCron;
use App\Admin\Core\Entity\OtpKey;
use App\Admin\Core\Entity\RefreshToken;
use App\Admin\Core\Enum\OtpType;
use App\Tests\Setup\KernelTestCase;
use Cesurapp\SwooleBundle\Cron\CronWorker;

class ClearTokenCronTest extends KernelTestCase
{
    public function testClearExpiredToken(): void
    {
        $user = $this->emSave($this->getUser());

        // Create Refresh Token
        $rf = $this->emSave((new RefreshToken())
            ->setOwner($user)
            ->setToken('123123123')
            ->setExpiredAt(new \DateTimeImmutable('-24 hour')));

        /** @var CronWorker $worker */
        $worker = static::getContainer()->get(CronWorker::class);
        $cron = $worker->get(ClearExpiredTokenCron::class);
        $cron();

        // Check Refresh Token
        $this->em()->clear();
        $this->assertNull($this->em()->getRepository(RefreshToken::class)->find($rf->getId()));
    }

    public function testClearOtpKey(): void
    {
        $user = $this->emSave($this->getUser());

        // Create OTP Key
        $rf = $this->emSave(
            (new OtpKey())
                ->setOwner($user)
                ->setType(OtpType::AUTH)
                ->setAddress($user->getEmail())
                ->setOtpKey(123445)
                ->setExpiredAt(new \DateTimeImmutable('-1 minute'))
        );

        /** @var CronWorker $worker */
        $worker = static::getContainer()->get(CronWorker::class);
        $cron = $worker->get(ClearExpiredOtpKeyCron::class);
        $cron();

        // Check OTP Key
        $this->em()->clear();
        $this->assertNull($this->em()->getRepository(OtpKey::class)->find($rf->getId()));
    }
}
