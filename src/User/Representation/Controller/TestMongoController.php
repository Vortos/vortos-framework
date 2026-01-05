<?php

namespace App\User\Representation\Controller;

use App\User\Application\Query\GetUser\GetUserQuery;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Bus\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ApiController]
#[Route(name: 'user.mongo', path: '/user/read')]
class TestMongoController
{
    public function __construct(
        private QueryBus $queryBus
    ) {}

    public function __invoke(): Response
    {
        $query = new GetUserQuery('019b8cbf-0a21-7bb2-a431-95697c6ed251');

        $result = $this->queryBus->ask($query);

        return new JsonResponse($result);
    }
}
