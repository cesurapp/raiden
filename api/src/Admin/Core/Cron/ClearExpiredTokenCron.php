<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\RefreshTokenRepository;
use Package\SwooleBundle\Cron\CronInterface;

/**
 * Clear Expired Refresh Token Cron.
 */
class ClearExpiredTokenCron implements CronInterface
{
    public const TIME = '@daily';
    public const ENABLE = true;

    public function __construct(private RefreshTokenRepository $repo)
    {
    }

    public function __invoke(mixed $data = null): void
    {
        $this->repo->clearExpiredToken();
    }
}
