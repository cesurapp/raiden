<?php

namespace Package\MediaBundle\Command;

use Package\MediaBundle\Repository\MediaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'media:status', description: 'View Media Storage Details')]
class MediaStatusCommand extends Command
{
    public function __construct(private readonly MediaRepository $repository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $totalFile = $this->repository->createQueryBuilder('m')
            ->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();
        $totalSize = $this->repository->createQueryBuilder('m')
            ->select('SUM(m.size)')->getQuery()->getSingleScalarResult();
        $totalUsedMedia = $this->repository->createQueryBuilder('m')
            ->select('SUM(m.counter)')->getQuery()->getSingleScalarResult();

        (new Table($output))
            ->setHeaders(['Total File', 'Total Size', 'Total Used Media'])
            ->setRows([
                [
                    $totalFile,
                    sprintf('%s MB / %s GB', number_format(round($totalSize / 1000)), number_format(round($totalSize / 1000 / 1000))),
                    $totalUsedMedia,
                ],
            ])
            ->setHorizontal()
            ->render();

        return Command::SUCCESS;
    }
}
