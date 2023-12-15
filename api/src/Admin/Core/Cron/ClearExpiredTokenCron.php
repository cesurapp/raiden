<?php

namespace App\Admin\Core\Cron;

use App\Admin\Core\Repository\RefreshTokenRepository;
use Cesurapp\SwooleBundle\Cron\AbstractCronJob;

/**
 * Clear Expired Refresh Token Cron.
 */
class ClearExpiredTokenCron extends AbstractCronJob
{
    public string $TIME = '@daily';

    public function __construct(private readonly RefreshTokenRepository $repo)
    {
    }

    public function __invoke(): void
    {
        $this->repo->clearExpiredToken();
    }
}
