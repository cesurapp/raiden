<?php

namespace Package\ApiBundle\ApiDocumentation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class ApiDocController extends AbstractController
{
    /**
     * View Developer API Documentation.
     */
    public function index(
        RouterInterface $router,
        ValidatorInterface $validator,
        Environment $twig
    ): Response {
        $exporter = new ApiDocExporter($router, $validator, $twig);

        return (new Response())->setContent($exporter->render());
    }
}
