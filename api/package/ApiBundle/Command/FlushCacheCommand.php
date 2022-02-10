<?php

namespace Package\ApiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear All Cache.
 */
class FlushCacheCommand extends Command
{
    protected static $defaultName = 'app:cache-clear';
    protected static $defaultDescription = 'Clear all Symfony & Doctrine cache';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getApplication()->find('cache:pool:clear')->run((new ArrayInput(['pools' => ['cache.global_clearer']])), $output);
        $this->getApplication()->find('cache:clear')->run(new ArrayInput(['--no-warmup' => true]), $output);

        return Command::SUCCESS;
    }
}
