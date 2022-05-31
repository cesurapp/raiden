<?php

namespace Package\ApiBundle\Thor\Command;

use Package\ApiBundle\Thor\Extractor\ThorExtractor;
use Package\ApiBundle\Thor\Generator\TypeScriptGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Thor Generate Api Documentation to JSON File.
 */
#[AsCommand(name: 'thor:generate', description: 'Thor Generate Api Documentation to JSON File')]
class ThorGenerateCommand extends Command
{
    public function __construct(private readonly RouterInterface $router, protected ParameterBagInterface $bag, protected ValidatorInterface $validator)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Generate
        $extractor = new ThorExtractor($this->router, $this->bag);
        $apiData = $extractor->extractData(true);
        $tsGenerator = new TypeScriptGenerator($apiData);

        // Create Directory
        $path = $this->bag->get('thor.storage_path');
        if (!file_exists($path) && !mkdir($path, recursive: true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        // Dump
        file_put_contents(
            sprintf('%s/%s.json', $path, time()),
            json_encode($apiData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );

        // Copy Custom Directory
        if ($this->bag->get('thor.ts_extra_path')) {
            $tsGenerator->generate()->copyFiles($path.'/Api');
        }

        return Command::SUCCESS;
    }
}
