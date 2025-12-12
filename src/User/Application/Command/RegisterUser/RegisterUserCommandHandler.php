<?php

namespace App\User\Application\Command\RegisterUser;

use Fortizan\Tekton\Bus\Command\Attribute\CommandHandler;

#[CommandHandler]
class RegisterUserCommandHandler
{
    public function __invoke(RegisterUserCommand $command)
    {
        echo ("User with name : " . $command->firstName . " is now registering. <br>");
    }
}
