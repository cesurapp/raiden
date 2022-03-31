<?php

namespace Package\ApiBundle\Thor\Controller;

use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Package\ApiBundle\Thor\Extractor\ThorExtractor;
use Package\ApiBundle\Thor\Generator\TypeScriptGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class ThorController extends AbstractController
{
    public function __construct(private RouterInterface $router, private ParameterBagInterface $bag)
    {
    }

    /**
     * View Thor API Documentation.
     */
    #[Route(path: '/thor/{version}', name: 'thor.view', defaults: ['version' => null])]
    #[Thor(desc: 'Thor Api Documentation', hidden: true, requireAuth: false)]
    public function view(?string $version): Response
    {
        $response = new Response();

        // Find Docs
        $docs = self::findDocs($this->getParameter('thor.storage_path'));

        // Render
        $generator = new ThorExtractor($this->router, $this->bag);

        return $response->setContent(
            $generator->render(!empty($docs[$version]) ? json_decode(
                $docs[$version]->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            ) : [], [
                'docs' => $docs,
                'version' => $version,
            ])
        );
    }

    /**
     * Download TypeScript Api.
     */
    #[Route(path: '/thor/{version}/download', name: 'thor.download')]
    #[Thor(desc: 'Thor Api Download', hidden: true, requireAuth: false)]
    public function download(?string $version): Response
    {
        // Find Docs
        $oldDocs = self::findDocs($this->getParameter('thor.storage_path'));

        $extractor = new ThorExtractor($this->router, $this->bag);

        // Generator
        $tsGenerator = new TypeScriptGenerator(
            !empty($oldDocs[$version]) ? json_decode(
                $oldDocs[$version]->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            ) : $extractor->extractData(true)
        );

        return ApiResponse::file($tsGenerator->generate()->compress());
    }

    /**
     * @return SplFileInfo[]
     */
    public static function findDocs(string $dir): array
    {
        $files = [];

        $finder = (new Finder())
            ->files()
            ->in($dir)
            ->name('*.json')
            ->sortByName(true)
            ->reverseSorting();

        foreach ($finder as $file) {
            $files[$file->getFilenameWithoutExtension()] = $file;
        }

        return $files;
    }
}
