<?php

namespace Package\ApiBundle\Documentation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiDocController extends AbstractController
{
    public function __construct(
        private RouterInterface $router,
        private ValidatorInterface $validator,
        private ParameterBagInterface $bag
    ) {
    }

    /**
     * View Developer API Documentation.
     */
    #[ApiDoc(desc: 'Api Documentation', requireAuth: false)]
    public function index(?string $version): Response
    {
        // Disable ENV
        if (!$this->bag->get('apidoc_prod') && 'prod' === $this->getParameter('kernel.environment')) {
            return new Response();
        }

        $docs = self::findDocs($this->getParameter('apidoc_path'));
        $response = $this->initResponse($docs, $version);

        // Replace Docs
        $response->setContent(
            str_replace('%docs%', json_encode(array_keys($docs), JSON_THROW_ON_ERROR), $response->getContent())
        );

        return $response;
    }

    private function initResponse(array $docs, ?string $version): Response
    {
        $response = new Response();

        // Custom Version
        if ($version && isset($docs[$version])) {
            return $response->setContent($docs[$version]->getContents());
        }

        // Dev Mode
        if ('dev' === $this->getParameter('kernel.environment')) {
            $generator = new ApiDocGenerator($this->router, $this->validator, $this->container->get('twig'), $this->bag);

            return $response->setContent($generator->render(true, [
                'baseUrl' => $this->bag->get('apidoc_base_url'),
            ]));
        }

        // Latest Version
        return $response->setContent($docs[0]->getContents());
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
            ->name('*.html')->sortByName(true)->reverseSorting();

        foreach ($finder as $file) {
            $files[$file->getFilenameWithoutExtension()] = $file;
        }

        return $files;
    }
}
