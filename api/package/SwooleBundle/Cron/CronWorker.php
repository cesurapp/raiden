<?php

namespace Package\SwooleBundle\Cron;

use Symfony\Component\DependencyInjection\ServiceLocator;

class CronWorker
{
    public function __construct(private ServiceLocator $locator)
    {
    }

    public function run(): void
    {
        foreach ($this->getActiveCron() as $cron) {
            try {
                $cron();
            } catch (\Exception $exception) {
                $this->failedCron($cron, $exception);
            }
        }
    }

    /**
     * @return CronInterface[]
     */
    private function getActiveCron(): array
    {
        return array_filter($this->locator->getProvidedServices(), static function (CronInterface $cron) {
            return $cron::ENABLED;
        });
    }

    private function failedCron(CronInterface $cron, \Exception $exception): void
    {

    }
}
