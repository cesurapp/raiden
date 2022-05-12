<?php

namespace Package\SwooleBundle\Cron;

use Cron\CronExpression;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class CronWorker
{
    private CronExpression $expression;

    public function __construct(private readonly ServiceLocator $locator, private readonly LoggerInterface $logger)
    {
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
            go(function () use ($cron) {
                try {
                    if ($cron::ENABLE && $cron->isDue) {
                        $this->logger->info('Cron Job Process: '.get_class($cron ?? ''));
                        $cron();
                        $this->logger->info('Cron Job Finish: '.get_class($cron ?? ''));
                    }
                } catch (\Exception $exception) {
                    $this->logger->error(
                        sprintf('CRON Job Failed: %s, exception: %s', get_class($cron ?? ''), $exception->getMessage())
                    );
                }
            });
        }
    }

    /**
     * Get CRON Instance.
     */
    public function get(string $class): ?CronInterface
    {
        if ($this->locator->has($class)) {
            $cron = $this->locator->get($class);

            if (!defined("$class::TIME")) {
                throw new CronConstantNotFoundException();
            }
            if (!defined("$class::ENABLE")) {
                throw new CronConstantNotFoundException('Cron ENABLE constant not found!');
            }

            $aliases = CronExpression::getAliases();
            $this->expression->setExpression($aliases[strtolower($cron::TIME)] ?? $cron::TIME);
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
