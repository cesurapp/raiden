<?php

namespace Package\ApiBundle\Documentation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class Controller extends AbstractController
{
    /**
     * View Developer API Documentation.
     */
    public function index(RouterInterface $router, ValidatorInterface $validator, Environment $twig): Response
    {
        $generator = new Generator($router, $validator, $twig);

        return (new Response())->setContent($generator->render());
    }
}
