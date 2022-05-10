<?php

namespace Package\SwooleBundle\Command;

use Package\SwooleBundle\Cron\CronInterface;
use Package\SwooleBundle\Cron\CronWorker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'cron:list', description: 'List Crons')]
class CronListCommand extends Command
{
    public function __construct(private readonly CronWorker $cronWorker)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        if (iterator_count($this->cronWorker->getAll())) {
            $output->table(['Cron Services', 'Enable', 'Time', 'Next'], array_map(static fn (CronInterface $cron) => [
                get_class($cron),
                $cron::ENABLE ? 'True' : 'False',
                $cron::TIME,
                $cron->next->format('Y-m-d H:i:s'), // @phpstan-ignore-line
            ], [...$this->cronWorker->getAll()]));
        } else {
            $output->warning('Cron job not found!');
        }

        return Command::SUCCESS;
    }
}
