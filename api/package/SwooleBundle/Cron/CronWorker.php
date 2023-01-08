<?php

namespace Package\SwooleBundle\Cron;

use Cron\CronExpression;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Lock\LockFactory;

class CronWorker
{
    private CronExpression $expression;

    public function __construct(
        private readonly ServiceLocator $locator,
        private readonly LoggerInterface $logger,
        private readonly LockFactory $lockFactory
    ) {
        // Predefined Constants
        $aliases = [
            '@EveryMinute' => '* * * * *',
            '@EveryMinute5' => '*/5 * * * *',
            '@EveryMinute10' => '*/10 * * * *',
            '@EveryMinute15' => '*/15 * * * *',
            '@EveryMinute30' => '*/30 * * * *',
        ];

        foreach ($aliases as $alias => $expr) {
            if (!CronExpression::supportsAlias($alias)) {
                CronExpression::registerAlias($alias, $expr);
            }
        }

        $this->expression = new CronExpression('* * * * *');
    }

    public function run(): void
    {
        foreach ($this->getAll() as $cron) {
            if (!$cron || !$cron->ENABLE || !$cron->isDue) {
                continue;
            }

            // Lock
            $lock = $this->lockFactory->createLock(get_class($cron), 1200, false);
            if (!$lock->acquire()) {
                continue;
            }

            go(function () use ($cron, $lock) {
                try {
                    $this->logger->info('Cron Job Process: '.get_class($cron));
                    $cron();
                    $this->logger->info('Cron Job Finish: '.get_class($cron));
                } catch (\Exception $exception) {
                    $this->logger->error(
                        sprintf('CRON Job Failed: %s, exception: %s', get_class($cron), $exception->getMessage())
                    );
                } finally {
                    $lock->release();
                }
            });
        }
    }

    /**
     * Get CRON Instance.
     */
    public function get(string $class): ?AbstractCronJob
    {
        if ($this->locator->has($class)) {
            /** @var AbstractCronJob $cron */
            $cron = $this->locator->get($class);
            $aliases = CronExpression::getAliases();
            $this->expression->setExpression($aliases[strtolower($cron->TIME)] ?? $cron->TIME);
            $cron->isDue = $this->expression->isDue();
            $cron->next = $this->expression->getNextRunDate();

            return $cron;
        }

        return null;
    }

    public function getAll(): \Traversable
    {
        foreach ($this->locator->getProvidedServices() as $cron => $value) {
            yield $this->get($cron);
        }

        return null;
    }
}
