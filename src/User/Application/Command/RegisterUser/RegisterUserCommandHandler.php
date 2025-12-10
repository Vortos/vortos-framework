<?php

namespace App\User\Application\Command\RegisterUser;

use Fortizan\Tekton\Attribute\Cqrs\AsCommandHandler;
use Fortizan\Tekton\Interface\CommandHandlerInterface;

#[AsCommandHandler]
class RegisterUserCommandHandler implements CommandHandlerInterface
{
    public function __invoke(RegisterUserCommand $command)
    {
        echo("User with name : " . $command->firstName . " is now registering. <br>");
    }
}
