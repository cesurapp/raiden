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
        $totalFile = $this->repository->createQueryBuilder('q')
            ->select('COUNT(q.id)')->getQuery()->getSingleScalarResult();
        $totalSize = $this->repository->createQueryBuilder('q')
            ->select('SUM(q.size)')->getQuery()->getSingleScalarResult();
        $totalUsedMedia = $this->repository->createQueryBuilder('q')
            ->select('SUM(q.counter)')->getQuery()->getSingleScalarResult();

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
