<?php

declare(strict_types=1);

namespace App\User\Application\EventHandler;

use Vortos\Messaging\Contract\DomainEventInterface;
use Vortos\Messaging\Hook\Attribute\BeforeDispatch;
use Psr\Log\LoggerInterface;

#[BeforeDispatch]
final class LogDispatchHook
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(DomainEventInterface $event): void
    {
        $this->logger->info('Hook: BeforeDispatch fired', [
            'event' => get_class($event)
        ]);
    }
}
