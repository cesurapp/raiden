<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\OtpKeyRepository;
use Package\SwooleBundle\Cron\AbstractCronJob;

/**
 * Clear Expired OTP Key Cron.
 */
class ClearExpiredOtpKeyCron extends AbstractCronJob
{
    public string $TIME = '@EveryMinute15';

    public function __construct(private readonly OtpKeyRepository $repo)
    {
    }

    public function __invoke(): void
    {
        $this->repo->clearExpired();
    }
}
