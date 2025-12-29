<?php

namespace App\User\Representation\Controller;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Query\DbalUserFinder;
use App\User\Infrastructure\Repository\DoctrineUserRepository;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Bus\Command\CommandBus;
use Fortizan\Tekton\Persistence\PersistenceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
            "laksura",
            "silva@gmail.com"
        );

        $this->commandbus->dispatch($command);

        return new JsonResponse("User Registered");
    }
}
