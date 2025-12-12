<?php

namespace App\User\Representation\Controller;

use App\User\Application\Query\GetUser\GetUserQuery;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Bus\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path:'/user/get', name:'user.show')]
#[ApiController]
class GetUserController
{

    public function __construct(
        private QueryBus $queryBus
    ){}

    public function __invoke(Request $request):Response
    {
        $response = $this->queryBus->ask(query: new GetUserQuery(userId: 1));
        return $response;
    }
}