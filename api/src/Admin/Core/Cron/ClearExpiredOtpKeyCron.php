<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\OtpKeyRepository;
use Package\SwooleBundle\Cron\AbstractCronJob;

/**
 * Clear Expired OTP Key Cron.
 */
class ClearExpiredOtpKeyCron extends AbstractCronJob
{
    public const TIME = '@EveryMinute15';

    public function __construct(private readonly OtpKeyRepository $repo)
    {
    }

    public function __invoke(mixed $data = null): void
    {
        $this->repo->clearExpired();
    }
}
