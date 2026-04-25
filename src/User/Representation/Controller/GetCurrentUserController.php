<?php

namespace App\User\Representation\Controller;

use App\User\Application\Query\GetUser\GetUserQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Vortos\Auth\Attribute\RequiresAuth;
use Vortos\Auth\Identity\CurrentUserProvider;
use Vortos\Cqrs\Query\QueryBusInterface;
use Vortos\Http\Attribute\ApiController;

#[ApiController]
#[Route('/api/users/me', methods: ['GET'])]
#[RequiresAuth]
final class GetCurrentUserController
{
    public function __construct(
        private CurrentUserProvider $currentUser,
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $identity = $this->currentUser->get();

        $user = $this->queryBus->ask(new GetUserQuery(userId: $identity->id()));

        return new JsonResponse($user);
    }
}