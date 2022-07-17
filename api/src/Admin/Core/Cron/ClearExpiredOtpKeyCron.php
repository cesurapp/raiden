<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\OtpKeyRepository;
use Package\SwooleBundle\Cron\CronInterface;

/**
 * Clear Expired OTP Key Cron.
 */
class ClearExpiredOtpKeyCron implements CronInterface
{
    public const TIME = '@EveryMinute15';
    public const ENABLE = true;

    public function __construct(private OtpKeyRepository $repo)
    {
    }

    public function __invoke(mixed $data = null): void
    {
        $this->repo->clearExpired();
    }
}
