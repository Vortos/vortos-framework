<?php

namespace App\User\Representation\Controller;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRegisterController{
    public function __construct(
        private MessageBusInterface $commandBus
    ){}

    public function __invoke(Request $request):Response
    {
        $this->commandBus->dispatch(message: new RegisterUserCommand("Sachintha", "abc@gmail.com"));
        return new Response("User is now Registered");
    }
}