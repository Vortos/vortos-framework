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
        $query = new GetUserQuery('019b6c0b-026a-7bf4-9b73-244394168acf');

        $result = $this->queryBus->ask($query);

        return new JsonResponse($result);
    }
}
