<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\RefreshTokenRepository;
use Package\SwooleBundle\Cron\AbstractCronJob;

/**
 * Clear Expired Refresh Token Cron.
 */
class ClearExpiredTokenCron extends AbstractCronJob
{
    public const TIME = '@daily';

    public function __construct(private readonly RefreshTokenRepository $repo)
    {
    }

    public function __invoke(mixed $data = null): void
    {
        $this->repo->clearExpiredToken();
    }
}
