<?php

namespace App\User\Representation\Controller;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use Vortos\Attribute\ApiController;
use Vortos\Bus\Command\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'user.db', path: 'user/write')]
#[ApiController]
class TestDoctrineController
{
    public function __construct(
        private CommandBus $commandbus
    ) {}

    public function __invoke(): Response
    {

        $command = new RegisterUserCommand(
            "whats",
            "going@on.here"
        );

        $this->commandbus->dispatch($command);

        return new JsonResponse(
            ['message' => 'User registered successfully'],
            Response::HTTP_CREATED
        );
    }
}
