<?php

namespace App\User\Application\EventHandler;

use App\User\Domain\Event\UserCreatedEvent;
use Vortos\Bus\Event\Attribute\EventHandler;
use Psr\Log\LoggerInterface;

#[EventHandler(group:'async', retries:2, delay:2000)]
class SendSmsHandler
{
    public function __construct(
        private LoggerInterface $logger
    ){
    }

    public function __invoke(UserCreatedEvent $event)
    {
        $this->logger->warning("Sending Sms...........");
        echo "Sending sms....";
    }
}