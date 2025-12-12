<?php

namespace Fortizan\Tekton\Bus\Command;

use Fortizan\Tekton\Bus\Command\Contract\CommandInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandBus{
    
    public function __construct(
        private MessageBusInterface $commandBus
    )
    {}

    public function dispatch(CommandInterface $command):void
    {
        $this->commandBus->dispatch(message: $command);
    }
}