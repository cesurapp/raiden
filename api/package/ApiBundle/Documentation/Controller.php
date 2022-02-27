<?php

namespace Package\ApiBundle\Documentation;

use Doctrine\ORM\EntityManagerInterface;
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
    #[ApiDoc(desc: 'Api Documentation', requireAuth: false)]
    public function index(RouterInterface $router, ValidatorInterface $validator, Environment $twig, EntityManagerInterface $entityManager): Response
    {
        $generator = new Generator($router, $validator, $twig, $this->getParameter('kernel.project_dir').'/config/api-doc.php');

        return (new Response())->setContent($generator->render());
    }
}
