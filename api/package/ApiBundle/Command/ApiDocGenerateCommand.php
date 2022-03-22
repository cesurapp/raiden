<?php

namespace Package\ApiBundle\Command;

use Package\ApiBundle\Documentation\ApiDocGenerator;
use Package\ApiBundle\Documentation\ApiDocTsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Generate Api Documentation to HTML File.
 */
class ApiDocGenerateCommand extends Command
{
    protected static $defaultName = 'apidoc:generate';
    protected static $defaultDescription = 'Generate Api Documentation to HTML File';

    public function __construct(
        private RouterInterface $router,
        private Environment $twig,
        protected ParameterBagInterface $bag
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Generate
        $path = $this->bag->get('apidoc_path');
        $generator = new ApiDocGenerator($this->router, $this->twig, $this->bag);
        $documentation = $generator->render(false, [
            'baseUrl' => $this->bag->get('apidoc_base_url'),
        ]);

        // Create Directory
        /*$path .= '/'.time();
        if (!mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }*/

        // Write to File
        /*$file = $path.'/'.time().'.html';
        file_put_contents($file, $documentation);*/

        $tsGenerator = new ApiDocTsGenerator($generator->extractData(true), $this->twig);
        $tsGenerator->generate();

        return Command::SUCCESS;
    }
}
