<?php

namespace App\Admin\Core\Controller;

use Package\ApiBundle\AbstractClass\AbstractApiController;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractApiController
{
    #[Thor(
        group: 'Home Page|0',
        desc: 'View Home Page',
        response: [200 => 'OK'],
        requireAuth: false
    )]
    #[Route(path: '/', methods: ['GET'])]
    public function home(): ApiResponse
    {
        return ApiResponse::create()->setData('OK');
    }
}
