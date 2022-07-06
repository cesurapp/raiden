<?php

namespace App\Admin\Core\Test;

use App\Admin\Core\Cron\ClearExpiredTokenCron;
use App\Admin\Core\Entity\RefreshToken;
use App\Admin\Core\Test\Setup\AbstractKernelTestCase;
use Package\SwooleBundle\Cron\CronWorker;

class CronTest extends AbstractKernelTestCase
{
    public function testClearExpiredToken(): void
    {
        $container = static::bootKernel()->getContainer();
        $user = $this->createUser();

        // Create Refresh Token
        $rf = (new RefreshToken())
            ->setOwner($user)
            ->setToken('123123123')
            ->setExpiredAt(new \DateTimeImmutable('-24 hour'));
        $this->save($rf);

        /** @var CronWorker $worker */
        $worker = $container->get(CronWorker::class);
        $cron = $worker->get(ClearExpiredTokenCron::class);
        $cron();

        // Check Refresh Token
        $this->manager()->clear();
        $this->assertNull($this->manager()->getRepository(RefreshToken::class)->find($rf->getId()));
    }
}
