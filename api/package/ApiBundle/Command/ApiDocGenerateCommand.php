<?php

namespace Package\ApiBundle\Command;

use Package\ApiBundle\Documentation\ApiDocGenerator;
use Package\ApiBundle\Documentation\ApiDocTsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
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
        $generator = new ApiDocGenerator($this->router, $this->bag);
        $data = $generator->extractData(true);
        $documentation = $this->twig->render('@Api/documentation.html.twig', [
            'data' => $data,
            'statusText' => Response::$statusTexts,
            'devMode' => false,
            'customData' => [
                'baseUrl' => $this->bag->get('apidoc_base_url'),
            ],
        ]);

        // Dump Documentation to HTML
        $path = $this->bag->get('apidoc_path').'/'.time();
        if (!mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
        file_put_contents($path.'/documentation.html', $documentation);

        // TypeScript Generator
        $tsGenerator = new ApiDocTsGenerator($data, $this->twig);
        $tsGenerator->generate()->compress($path);

        return Command::SUCCESS;
    }
}
