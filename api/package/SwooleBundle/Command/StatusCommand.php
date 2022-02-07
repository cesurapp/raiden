<?php

namespace Package\SwooleBundle\Command;

use Swoole\Client;
use Swoole\Event;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'server:status', description: 'Status Swoole Server')]
class StatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ConsoleSectionOutput $section */
        $section = $output->section();
        $io = new SymfonyStyle($input, $section);

        $client = new Client(SWOOLE_SOCK_TCP);
        $client->connect('0.0.0.0', 9502);

        go(static function () use ($io, $client, $section) {
            while (true) {
                // Get Data
                $client->send('OPENSWOOLE_STATS_JSON');
                $data = json_decode($client->recv(), true, 512, JSON_THROW_ON_ERROR);

                // Write
                $section->clear();

                $table = $io->createTable();
                $table->setRows([
                    ['Version', $data['version'], ''],
                    ['Process ID', 'Master > ' . $data['master_pid'], 'Manager > ' . $data['manager_pid']],
                    ['Worker', 'Idle > ' . $data['workers_idle'], 'Total > ' . $data['workers_total']],
                    ['Task Worker', 'Idle > ' . $data['task_workers_idle'], 'Total > ' . $data['task_workers_total'], 'Current > ' . $data['tasking_num']],
                    ['Connection', 'Max > ' . $data['max_conn'], 'Accepted > ' . $data['requests_total'], 'Active > ' . $data['connections_accepted']],
                    ['Memory', ((int)$data['worker_memory_usage'] / (1024 * 1024)) . 'mb']
                ]);
                $table->render();

                // Wait
                sleep(1);
            }
        });
        Event::wait();

        return Command::SUCCESS;
    }
}