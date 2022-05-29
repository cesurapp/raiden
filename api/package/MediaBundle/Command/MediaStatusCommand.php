<?php

namespace Package\MediaBundle\Command;

use Package\MediaBundle\Repository\MediaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'media:status', description: 'View Media Storage Details')]
class MediaStatusCommand extends Command
{
    public function __construct(private MediaRepository $repository)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $singleCount = $this->repository->createQueryBuilder('m')
            ->groupBy('m.storage')
            ->select('COUNT(m.id)');
    }
}
