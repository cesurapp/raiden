<?php

namespace App\Core\Controller;

use App\Core\Entity\UserEntity;
use App\Core\Request\LoginRequest;
use Package\ApiBundle\Documentation\ApiDoc;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Response\ResponseTypeEnum;
use Package\SwooleBundle\Repository\FailedTaskRepository;
use Package\SwooleBundle\Task\TaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    /**
     * @param Request $request
     * @return ApiResponse
     */
    #[Route(path: '/', name: 'home')]
    #[ApiDoc(desc: 'View Home Page', dto: LoginRequest::class, query: [
        'id' => 'asdsad',
        'name' => 'int',
    ], rSuccess: [
        'id' => 222,
        'name' => 'FakOff',
    ])]
    public function index(Request $request, FailedTaskRepository $f): ApiResponse
    {
        /*for ($i = 0; $i < 100; ++$i) {
            TaskHandler::dispatch(TestTask::class, ['deneme', 'nalet']);
        }*/
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

        return ApiResponse::create()
            //->setQuery($f->createQueryBuilder('f'))
                ->setType(ResponseTypeEnum::ApiInfo)
            ->setData(['tamam', 'asdasdasd asdasdas'])
            ;
    }

    #[Route(path: '/post/{id}/{nm}', name: 'show_all', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[Route(path: '/post/show/{id}/{nm}', name: 'show_post', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[Route(path: '/post/edit/{id}/{nm}', name: 'show_edit', requirements: ['id' => "\d+"], methods: ['GET'])]
    #[ApiDoc(desc: '', query: ['page' => 'asdas', 'limit' => '10'])]
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
