<?php

namespace App\Admin\Core\Controller;

use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends ApiController
{
    #[Thor(
        stack: 'Home Page|0',
        title: 'View Home Page',
        isHidden: true,
        isAuth: false
    )]
    #[Route(path: '/', methods: ['GET'])]
    public function home(): ApiResponse
    {
        return ApiResponse::create()->setData('OK');
    }
}
