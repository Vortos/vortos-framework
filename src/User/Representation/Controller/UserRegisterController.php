<?php

namespace App\User\Representation\Controller;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Bus\Command\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path:'/user/register', name:'user.register')]
#[ApiController]
class UserRegisterController{
    public function __construct(
        private CommandBus $commandBus
    ){}

    public function __invoke(Request $request):Response
    {
        $this->commandBus->dispatch(command: new RegisterUserCommand("Sachintha", "abc@gmail.com"));
        return new Response("User is now Registered");
    }
}