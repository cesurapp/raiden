<?php

namespace App\Core\Controller;

use App\Core\Entity\UserEntity;
use App\Core\Request\LoginRequest;
use App\Core\Task\TestTask;
use Package\ApiBundle\Attribute\ApiDoc;
use Package\ApiBundle\Utils\ApiResponse;
use Package\SwooleBundle\Task\TaskLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AuthController extends AbstractController
{
    #[Route(path: '/', name: 'home')]
    #[ApiDoc(description: 'View Home Page', apiDto: LoginRequest::class, query: [
        'id' => 'asdsad',
        'name' => 'int',
    ], response: [
        'id' => 222,
        'name' => 'FakOff',
    ])]
    public function index(Request $request, CacheInterface $adapter, TestTask $locator): JsonResponse
    {
        dump($locator);
        //dump($GLOBALS['server']);
        //  dump($adapter);

        //$handler->dispatch('sadasd', ['asd', 1,2,3]);
        //$logger->error('sdsadasdas');
        //$logger->info('sdsadasdas');
        //$logger->warning('sdsadasdas');
        //$client = new Client(SWOOLE_SOCK_TCP);
        //dump($client->connect('swoole://task'));
        //$server = $request->attributes->get('_server');
        //$server->task(['asd']);
        //$server->task(['asd']);
        //$server->task(['asd']);
        //$server->task(['asd']);
        //$server->task(['asd']);
        //dump($server->master_pid);
        /*go(function (){
            Coroutine::sleep(60);
        });*/
        //Process::kill($server->master_pid, SIGUSR1);
        //dump($handler->dispatch());

        return ApiResponse::json(['asdddddsa']);
    }

    #[Route(path: '/post/{id}/{nm}', name: 'show_all', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[Route(path: '/post/show/{id}/{nm}', name: 'show_post', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[Route(path: '/post/edit/{id}/{nm}', name: 'show_edit', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[ApiDoc(description: '', query: ['page' => 'asdas', 'limit' => '10'])]
    public function show(Request $request, ValidatorInterface $validator, int|UserEntity $id, int $a = 0): JsonResponse
    {
        return ApiResponse::json(['asdsa']);
    }

    #[Route(path: '/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        /* $wg = new WaitGroup();
         $results = [];

         go(static function () use ($wg, &$results) {
             $wg->add();
             Coroutine::sleep(2);
             $results[] = 'a';
             $wg->done();
         });

         go(static function () use ($wg, &$results) {
             $wg->add();
             Coroutine::sleep(1);
             $results[] = 'b';
             $wg->done();
         });

         $wg->wait();*/

        return ApiResponse::json(['sds']);
    }

    #[Route(path: '/test2', name: 'test', methods: ['GET'])]
    public function test2(): JsonResponse
    {
        return ApiResponse::json(['fakoff']);
    }
}
