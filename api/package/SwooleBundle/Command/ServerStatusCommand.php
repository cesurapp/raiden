<?php

namespace Package\SwooleBundle\Command;

use Swoole\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'server:status', description: 'Status Swoole Server')]
class ServerStatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ConsoleSectionOutput $section */
        $section = $output->section(); // @phpstan-ignore-line
        $output = new SymfonyStyle($input, $section);
        $client = new Client(SWOOLE_SOCK_TCP);

        /* @phpstan-ignore-next-line */
        while (true) {
            try {
                if (!$client->isConnected()) {
                    $client->connect('0.0.0.0', 9502, 1.5);
                } else {
                    $client->send('metrics');
                    $data = json_decode($client->recv(), true, 512, JSON_THROW_ON_ERROR);

                    $section->clear();
                    $table = $output->createTable();
                    $table->setRows([
                        ['Version', $data['metrics']['version'], ''],
                        ['Environment', $data['server']['app']['env'], ''],
                        ['Host', $data['server']['http']['host'].':'.$data['server']['http']['port'], ''],
                        ['TCP Host', $data['server']['tcp']['host'].':'.$data['server']['tcp']['port'], ''],
                        ['Cron Worker', $data['server']['app']['cron'] ? 'True' : 'False', ''],
                        ['Task Worker', $data['server']['app']['task'] ? 'True' : 'False', ''],
                        [
                            'Process ID',
                            'Master > '.$data['metrics']['master_pid'],
                            'Manager > '.$data['metrics']['manager_pid'],
                        ],
                        [
                            'Worker',
                            'Idle > '.$data['metrics']['workers_idle'],
                            'Total > '.$data['metrics']['workers_total'],
                        ],
                        [
                            'Task Worker',
                            'Idle > '.$data['metrics']['task_workers_idle'],
                            'Total > '.$data['metrics']['task_workers_total'],
                            'Queue > '.$data['metrics']['tasking_num'],
                        ],
                        [
                            'Connection',
                            'Max > '.number_format($data['metrics']['max_conn']),
                            'Total > '.number_format($data['metrics']['requests_total']),
                            'Active > '.$data['metrics']['connections_active'],
                        ],
                        [
                            'Cache Table',
                            'Current > '.$data['server']['cache_table']['current'],
                            'Total > '.$data['server']['cache_table']['size'],
                        ],
                        [
                            'Memory',
                            ((int) $data['metrics']['worker_memory_usage'] / (1024 * 1024)).'mb',
                            'VM Object > '.$data['metrics']['worker_vm_object_num'],
                            'VM Resource > '.$data['metrics']['worker_vm_resource_num'],
                        ],
                    ]);
                    $table->render();
                }
            } catch (\Exception $exception) {
                $section->clear();
                $output->error("Could not connect to server!\n".$exception->getMessage());
                $client = new Client(SWOOLE_SOCK_TCP);
            }

            usleep(1500000);
        }

        return Command::SUCCESS;
    }
}